import { createFileRoute } from "@tanstack/react-router";
import ProductForm from "@/components/admin/product/ProductForm";

export const Route = createFileRoute("/admin/product/add")({
  component: AddProductPage,
});

function AddProductPage() {
  return (
    <div className="mx-auto max-w-7xl space-y-6 p-6">
      <div>
        <h1 className="text-3xl font-bold">Add Product</h1>
        <p className="text-muted-foreground">
          Create a new product for your store.
        </p>
      </div>

      <ProductForm />
    </div>
  );
}