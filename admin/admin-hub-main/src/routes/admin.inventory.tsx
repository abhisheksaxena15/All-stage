import { createFileRoute } from "@tanstack/react-router";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useState } from "react";
import { toast } from "sonner";
import { Edit2, Package, AlertTriangle, ShieldCheck, XCircle } from "lucide-react";
import { adminApi, AdminApiError } from "@/lib/admin-api";
import type { Paginated, Product } from "@/lib/admin-types";
import { PageHeader } from "@/components/admin/ui";
import { Button, Input, Select } from "@/components/admin/Form";
import { DataTable, type Column } from "@/components/admin/DataTable";
import { Modal } from "@/components/admin/Modal";

export const Route = createFileRoute("/admin/inventory")({
  component: InventoryPage,
});

function InventoryPage() {
  const qc = useQueryClient();
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(25);
  const [search, setSearch] = useState("");
  const [stockFilter, setStockFilter] = useState<string>(""); // "", "in_stock", "low_stock", "out_of_stock"
  
  // Modal states for manual stock updates
  const [editProduct, setEditProduct] = useState<Product | null>(null);
  const [newQuantity, setNewQuantity] = useState("");
  const [newThreshold, setNewThreshold] = useState("");

  const productsQuery = useQuery({
    queryKey: ["admin", "products", "inventory", { page, perPage, search, stockFilter }],
    queryFn: async () => {
      const p = new URLSearchParams();
      p.set("page", String(page));
      p.set("per_page", String(perPage));
      if (search) p.set("search", search);
      
      // Fetch all products first
      const res = await adminApi.get<Paginated<Product>>(`/products?${p.toString()}`);
      
      // Filter products based on stock status client-side if needed
      if (stockFilter && res.data) {
        res.data = res.data.filter(prod => {
          const qty = prod.quantity ?? 0;
          const thresh = prod.low_stock_threshold ?? 10;
          if (stockFilter === "out_of_stock") return qty === 0;
          if (stockFilter === "low_stock") return qty > 0 && qty <= thresh;
          if (stockFilter === "in_stock") return qty > thresh;
          return true;
        });
        res.total = res.data.length;
      }
      return res;
    },
    retry: false,
  });

  const updateStockMutation = useMutation({
    mutationFn: ({ id, qty, threshold }: { id: string | number; qty: number; threshold: number }) =>
      adminApi.put(`/products/${id}/inventory`, { quantity: qty, low_stock_threshold: threshold }),
    onSuccess: () => {
      toast.success("Inventory stock levels updated");
      qc.invalidateQueries({ queryKey: ["admin", "products"] });
      setEditProduct(null);
    },
    onError: (e) => toast.error(e instanceof AdminApiError ? e.message : "Failed to update stock"),
  });

  const handleOpenEdit = (prod: Product) => {
    setEditProduct(prod);
    setNewQuantity(String(prod.quantity ?? 0));
    setNewThreshold(String(prod.low_stock_threshold ?? 10));
  };

  const handleSave = () => {
    if (!editProduct) return;
    const qty = parseInt(newQuantity);
    const threshold = parseInt(newThreshold);
    if (isNaN(qty) || qty < 0 || isNaN(threshold) || threshold < 0) {
      toast.error("Please enter valid, non-negative values");
      return;
    }
    updateStockMutation.mutate({ id: editProduct.id, qty, threshold });
  };

  const columns: Column<Product>[] = [
    {
      header: "Product",
      key: "name",
      render: (row) => (
        <div className="flex items-center gap-3">
          {row.primary_image_url ? (
            <img src={row.primary_image_url} alt={row.name} className="h-10 w-10 rounded-md object-cover border" />
          ) : (
            <div className="grid h-10 w-10 place-items-center rounded-md bg-muted text-muted-foreground border">
              <Package className="h-5 w-5" />
            </div>
          )}
          <div>
            <span className="font-semibold text-foreground">{row.name}</span>
          </div>
        </div>
      ),
    },
    {
      header: "SKU",
      key: "sku",
      render: (row) => <span className="font-mono text-xs">{row.sku}</span>,
    },
    {
      header: "Available Stock",
      key: "quantity",
      render: (row) => <span className="font-semibold">{row.quantity ?? 0} units</span>,
    },
    {
      header: "Low Threshold",
      key: "low_stock_threshold",
      render: (row) => <span>{row.low_stock_threshold ?? 10} units</span>,
    },
    {
      header: "Stock Status",
      key: "quantity",
      render: (row) => {
        const qty = row.quantity ?? 0;
        const thresh = row.low_stock_threshold ?? 10;
        
        if (qty === 0) {
          return (
            <span className="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">
              <XCircle className="h-3.5 w-3.5" /> Out of Stock
            </span>
          );
        }
        if (qty <= thresh) {
          return (
            <span className="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">
              <AlertTriangle className="h-3.5 w-3.5" /> Low Stock
            </span>
          );
        }
        return (
          <span className="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">
            <ShieldCheck className="h-3.5 w-3.5" /> In Stock
          </span>
        );
      },
    },
    {
      header: "Actions",
      key: "id",
      render: (row) => (
        <Button size="sm" variant="outline" onClick={() => handleOpenEdit(row)} className="flex items-center gap-1.5">
          <Edit2 className="h-3 w-3" /> Edit Stock
        </Button>
      ),
    },
  ];

  const productsList = productsQuery.data?.data ?? [];
  const total = productsQuery.data?.total ?? 0;

  return (
    <div className="space-y-6">
      <PageHeader title="Inventory Management" subtitle="Monitor and manage real-time product stock levels" />

      {productsQuery.isError && (
        <div className="mb-4 rounded-md border border-amber-500/40 bg-amber-500/10 p-3 text-xs text-amber-800 dark:text-amber-300">
          {productsQuery.error instanceof AdminApiError ? productsQuery.error.message : "Could not load products."}
        </div>
      )}

      <div className="rounded-xl border bg-card">
        <DataTable<Product>
          columns={columns}
          rows={productsList}
          total={total}
          page={page}
          perPage={perPage}
          loading={productsQuery.isFetching}
          search={search}
          onSearch={(v) => {
            setSearch(v);
            setPage(1);
          }}
          onPageChange={setPage}
          onPerPageChange={(n) => {
            setPerPage(n);
            setPage(1);
          }}
          rowKey={(r) => r.id}
          emptyText="No inventory products found."
          filters={
            <Select
              value={stockFilter}
              onChange={(e) => {
                setStockFilter(e.target.value);
                setPage(1);
              }}
              className="h-9 w-44"
            >
              <option value="">All Stock Levels</option>
              <option value="in_stock">In Stock</option>
              <option value="low_stock">Low Stock</option>
              <option value="out_of_stock">Out of Stock</option>
            </Select>
          }
        />
      </div>

      {/* Edit Stock Modal */}
      {editProduct && (
        <Modal isOpen={!!editProduct} onClose={() => setEditProduct(null)} title="Update Stock Levels">
          <div className="space-y-5 py-3">
            <div>
              <p className="text-sm font-semibold text-foreground">{editProduct.name}</p>
              <p className="text-xs text-muted-foreground font-mono">SKU: {editProduct.sku}</p>
            </div>
            
            <div className="grid gap-4 sm:grid-cols-2">
              <div>
                <label className="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">
                  Stock Quantity
                </label>
                <Input
                  type="number"
                  min="0"
                  value={newQuantity}
                  onChange={(e) => setNewQuantity(e.target.value)}
                />
              </div>
              
              <div>
                <label className="block text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-1">
                  Low Stock Threshold
                </label>
                <Input
                  type="number"
                  min="0"
                  value={newThreshold}
                  onChange={(e) => setNewThreshold(e.target.value)}
                />
              </div>
            </div>

            <div className="flex items-center justify-end gap-3 pt-4 border-t">
              <Button variant="outline" onClick={() => setEditProduct(null)}>
                Cancel
              </Button>
              <Button onClick={handleSave} loading={updateStockMutation.isPending}>
                Save Changes
              </Button>
            </div>
          </div>
        </Modal>
      )}
    </div>
  );
}
