/**
 * Reusable product form used by both create (new) and edit routes.
 * Submits multipart/form-data so images upload in the same request.
 */
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useNavigate, Link } from "@tanstack/react-router";
import { useEffect, useState } from "react";
import { toast } from "sonner";
import { ArrowLeft, Save } from "lucide-react";
import { adminApi, AdminApiError } from "@/lib/admin-api";
import type { Brand, Category, Product, ProductImage, Subcategory } from "@/lib/admin-types";
import { PageHeader, Card } from "@/components/admin/ui";
import { Button, Field, Input, Select, Textarea } from "@/components/admin/Form";
import { ImageUploader } from "@/components/admin/ImageUploader";

interface FormState {
  name: string;
  slug: string;
  sku: string;
  short_description: string;
  description: string;
 selling_price: string;
compare_price: string;
cost_price: string;
  category_id: string;
  subcategory_id: string;
  brand_id: string;
  featured: boolean;
status: "ACTIVE" | "DRAFT" | "ARCHIVED";}

const empty: FormState = {
  name: "",
  slug: "",
  sku: "",
  short_description: "",
  description: "",
 selling_price: "",
compare_price: "",
cost_price: "",
  category_id: "",
  subcategory_id: "",
  brand_id: "",
  featured: false,
  status: "active",
};

function slugify(s: string) {
  return s.toLowerCase().trim().replace(/[^\w\s-]/g, "").replace(/[\s_-]+/g, "-").replace(/^-+|-+$/g, "");
}

export function ProductForm({ productId }: { productId?: string }) {
  const isEdit = !!productId;
  const navigate = useNavigate();
  const qc = useQueryClient();

  const [form, setForm] = useState<FormState>(empty);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [newImages, setNewImages] = useState<File[]>([]);
  const [existingImages, setExistingImages] = useState<ProductImage[]>([]);
  const [removedImageIds, setRemovedImageIds] = useState<(string | number)[]>([]);
  const [slugDirty, setSlugDirty] = useState(false);
  const [primaryImageId, setPrimaryImageId] = useState<string | number | null>(null);
  const [primaryImageIndex, setPrimaryImageIndex] = useState<number | null>(null);

  const handleSetPrimary = (type: "existing" | "new", val: string | number) => {
    if (type === "existing") {
      setPrimaryImageId(val);
      setPrimaryImageIndex(null);
    } else {
      setPrimaryImageId(null);
      setPrimaryImageIndex(Number(val));
    }
  };

  const existing = useQuery({
    queryKey: ["admin", "products", productId],
    queryFn: () => adminApi.get<Product>(`/products/${productId}`),
    enabled: isEdit,
    retry: false,
  });

  useEffect(() => {
    if (!existing.data) return;
    const p = existing.data;
    setForm({
      name: p.name ?? "",
      slug: p.slug ?? "",
      sku: p.sku ?? "",
      short_description: p.short_description ?? "",
      description: p.description ?? "",
      selling_price: p.selling_price != null ? String(p.selling_price) : "",
      compare_price: p.compare_price != null ? String(p.compare_price) : "",
      cost_price: String(p.cost_price ?? 0),
      category_id: p.category_id != null ? String(p.category_id) : "",
      subcategory_id: p.subcategory_id != null ? String(p.subcategory_id) : "",
      brand_id: p.brand_id != null ? String(p.brand_id) : "",
      featured: !!p.featured,
      status: p.status ?? "active",
    });
    setExistingImages(p.images ?? []);
    setSlugDirty(true);

    const primaryImg = (p.images ?? []).find((i) => i.is_primary);
    if (primaryImg) {
      setPrimaryImageId(primaryImg.id ?? null);
      setPrimaryImageIndex(null);
    } else if ((p.images ?? []).length > 0) {
      setPrimaryImageId(p.images[0].id ?? null);
      setPrimaryImageIndex(null);
    }
  }, [existing.data]);

  const categories = useQuery({
    queryKey: ["admin", "categories", "options"],
    queryFn: () => adminApi.get<Category[]>("/categories?per_page=200"),
    retry: false,
  });
  const brands = useQuery({
    queryKey: ["admin", "brands", "options"],
    queryFn: () => adminApi.get<Brand[]>("/brands?per_page=200"),
    retry: false,
  });
  const subcategories = useQuery({
    queryKey: ["admin", "subcategories", "options", form.category_id],
    queryFn: () =>
      adminApi.get<Subcategory[]>(
        `/subcategories?per_page=200${form.category_id ? `&category_id=${form.category_id}` : ""}`,
      ),
    enabled: !!form.category_id,
    retry: false,
  });

  const update = (patch: Partial<FormState>) =>
    setForm((f) => {
      const next = { ...f, ...patch };
      if ("name" in patch && !slugDirty) next.slug = slugify(patch.name || "");
      return next;
    });

  const validate = (): boolean => {
    const e: Record<string, string> = {};
    if (!form.name.trim()) e.name = "Required";
    if (!form.slug.trim()) e.slug = "Required";
    if (!form.selling_price || Number(form.selling_price) < 0) e.selling_price = "Enter a valid selling_price";
    if (form.compare_price && Number(form.compare_price) >= Number(form.selling_price))
      e.compare_price = "Must be lower than selling_price";
    if (Number(form.cost_price) < 0) e.cost_price = "Cannot be negative";
    setErrors(e);
    return Object.keys(e).length === 0;
  };

  const submit = useMutation({
    mutationFn: async () => {
      const fd = new FormData();
      Object.entries(form).forEach(([k, v]) => {
        if (v === "" || v == null) return;
        fd.append(k, typeof v === "boolean" ? (v ? "1" : "0") : String(v));
      });
      newImages.forEach((f) => fd.append("images[]", f));
      removedImageIds.forEach((id) => fd.append("removed_image_ids[]", String(id)));
      if (primaryImageId !== null) {
        fd.append("primary_image_id", String(primaryImageId));
      }
      if (primaryImageIndex !== null) {
        fd.append("primary_image_index", String(primaryImageIndex));
      }
      if (isEdit) {
        fd.append("_method", "PUT"); // PHP-friendly multipart update
        return adminApi.post<Product>(`/products/${productId}`, fd);
      }
      return adminApi.post<Product>("/products", fd);
    },
    onSuccess: () => {
      toast.success(isEdit ? "Product updated" : "Product created");
      qc.invalidateQueries({ queryKey: ["admin", "products"] });
      navigate({ to: "/admin/products" });
    },
    onError: (err) => {
      if (err instanceof AdminApiError && err.data && typeof err.data === "object") {
        const data = err.data as { errors?: Record<string, string[]>; message?: string };
        if (data.errors) {
          const mapped: Record<string, string> = {};
          for (const [k, v] of Object.entries(data.errors)) mapped[k] = v[0];
          setErrors(mapped);
        }
        toast.error(data.message || err.message);
      } else {
        toast.error("Save failed");
      }
    },
  });

  const onSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!validate()) return;
    submit.mutate();
  };

  return (
    <div className="mx-auto max-w-5xl">
      <PageHeader
        title={isEdit ? "Edit product" : "Add product"}
        description={isEdit ? "Update product details and media." : "Create a new product for your catalog."}
        actions={
          <Link
            to="/admin/products"
            className="inline-flex h-9 items-center gap-2 rounded-md border border-border bg-background px-3 text-sm hover:bg-muted"
          >
            <ArrowLeft className="h-4 w-4" /> Back
          </Link>
        }
      />

      <form onSubmit={onSubmit} className="grid gap-6 lg:grid-cols-3">
        <div className="space-y-6 lg:col-span-2">
          <Card className="p-5">
            <h3 className="mb-4 text-sm font-semibold">Basics</h3>
            <div className="grid gap-4">
              <Field label="Name" required error={errors.name}>
                <Input
                  value={form.name}
                  onChange={(e) => update({ name: e.target.value })}
                  placeholder="Heavyweight Oversized Tee"
                />
              </Field>
              <div className="grid gap-4 sm:grid-cols-2">
                <Field label="Slug" required error={errors.slug} hint="URL-friendly identifier">
                  <Input
                    value={form.slug}
                    onChange={(e) => {
                      setSlugDirty(true);
                      update({ slug: slugify(e.target.value) });
                    }}
                    placeholder="heavyweight-oversized-tee"
                  />
                </Field>
                <Field label="SKU" error={errors.sku}>
                  <Input
                    value={form.sku}
                    onChange={(e) => update({ sku: e.target.value })}
                    placeholder="ALS-TEE-001"
                  />
                </Field>
              </div>
              <Field label="Short description" error={errors.short_description}>
                <Textarea
                  rows={2}
                  value={form.short_description}
                  onChange={(e) => update({ short_description: e.target.value })}
                  placeholder="One-liner shown on cards and search."
                />
              </Field>
              <Field label="Description" error={errors.description}>
                <Textarea
                  rows={8}
                  value={form.description}
                  onChange={(e) => update({ description: e.target.value })}
                  placeholder="Full product description, materials, fit…"
                />
              </Field>
            </div>
          </Card>

          <Card className="p-5">
            <h3 className="mb-4 text-sm font-semibold">Media</h3>
            <ImageUploader
              multiple
              value={newImages}
              onChange={(files) => {
                setNewImages(files);
                if (primaryImageIndex !== null && primaryImageIndex >= files.length) {
                  setPrimaryImageIndex(files.length > 0 ? 0 : null);
                }
                if (primaryImageId === null && primaryImageIndex === null && files.length > 0) {
                  setPrimaryImageIndex(0);
                }
              }}
              existing={existingImages.map((i) => ({ id: i.id, url: i.url }))}
              onRemoveExisting={(id, idx) => {
                if (id != null) setRemovedImageIds((prev) => [...prev, id]);
                setExistingImages((prev) => {
                  const next = prev.filter((_, i) => i !== idx);
                  if (primaryImageId !== null && String(id) === String(primaryImageId)) {
                    if (next.length > 0) {
                      setPrimaryImageId(next[0].id ?? null);
                    } else if (newImages.length > 0) {
                      setPrimaryImageId(null);
                      setPrimaryImageIndex(0);
                    } else {
                      setPrimaryImageId(null);
                      setPrimaryImageIndex(null);
                    }
                  }
                  return next;
                });
              }}
              primaryId={primaryImageId}
              primaryIndex={primaryImageIndex}
              onSetPrimary={handleSetPrimary}
            />
          </Card>

          <Card className="p-5">
            <h3 className="mb-4 text-sm font-semibold">Pricing & inventory</h3>
            <div className="grid gap-4 sm:grid-cols-3">
              <Field label="selling_price (₹)" required error={errors.selling_price}>
                <Input
                  type="number"
                  min="0"
                  step="0.01"
                  value={form.selling_price}
                  onChange={(e) => update({ selling_price: e.target.value })}
                />
              </Field>
              <Field label="Discount selling_price (₹)" error={errors.compare_price}>
                <Input
                  type="number"
                  min="0"
                  step="0.01"
                  value={form.compare_price}
                  onChange={(e) => update({ compare_price: e.target.value })}
                />
              </Field>
              <Field label="Cost price (₹)" required error={errors.cost_price}>
                <Input
                  type="number"
                  min="0"
                  step="0.01"
                  value={form.cost_price}
                  onChange={(e) => update({ cost_price: e.target.value })}
                />
              </Field>
            </div>
          </Card>
        </div>

        <div className="space-y-6">
          <Card className="p-5">
            <h3 className="mb-4 text-sm font-semibold">Organization</h3>
            <div className="space-y-4">
              <Field label="Category" error={errors.category_id}>
                <Select
                  value={form.category_id}
                  onChange={(e) => update({ category_id: e.target.value, subcategory_id: "" })}
                >
                  <option value="">— Select —</option>
                  {(categories.data ?? []).map((c) => (
                    <option key={c.id} value={c.id}>{c.name}</option>
                  ))}
                </Select>
              </Field>
              <Field label="Subcategory" error={errors.subcategory_id}>
                <Select
                  value={form.subcategory_id}
                  onChange={(e) => update({ subcategory_id: e.target.value })}
                  disabled={!form.category_id}
                >
                  <option value="">— Select —</option>
                  {(subcategories.data ?? []).map((s) => (
                    <option key={s.id} value={s.id}>{s.name}</option>
                  ))}
                </Select>
              </Field>
              <Field label="Brand" error={errors.brand_id}>
                <Select
                  value={form.brand_id}
                  onChange={(e) => update({ brand_id: e.target.value })}
                >
                  <option value="">— Select —</option>
                  {(brands.data ?? []).map((b) => (
                    <option key={b.id} value={b.id}>{b.name}</option>
                  ))}
                </Select>
              </Field>
            </div>
          </Card>

          <Card className="p-5">
            <h3 className="mb-4 text-sm font-semibold">Visibility</h3>
            <div className="space-y-4">
              <Field label="Status">
                <Select
                  value={form.status}
                  onChange={(e) => update({ status: e.target.value as "ACTIVE" | "DRAFT" | "ARCHIVED" })}
                >
                  <option value="ACTIVE">Active</option>
                  <option value="DRAFT">Draft</option>
                  <option value="ARCHIVED">Archived</option>
                </Select>
              </Field>
              <label className="flex items-center gap-2 text-sm">
                <input
                  type="checkbox"
                  checked={form.featured}
                  onChange={(e) => update({ featured: e.target.checked })}
                  className="h-4 w-4"
                />
                Featured product
              </label>
            </div>
          </Card>

          <Button type="submit" disabled={submit.isPending} className="w-full">
            <Save className="h-4 w-4" />
            {submit.isPending ? "Saving…" : isEdit ? "Save changes" : "Create product"}
          </Button>
        </div>
      </form>
    </div>
  );
}
