import type { Product } from "@/types/product";

interface Props {
  product: Product;
  setProduct: React.Dispatch<React.SetStateAction<Product>>;
}

export default function ProductInventory({
  product,
  setProduct,
}: Props) {
  return (
    <div className="rounded-xl border bg-white p-6 shadow-sm">

      <h2 className="mb-6 text-xl font-semibold">
        Inventory
      </h2>

      <div className="grid gap-5 md:grid-cols-2">

        <div>
          <label className="mb-2 block text-sm font-medium">
            cost_price Quantity
          </label>

          <input
            type="number"
            value={product.quantity}
            onChange={(e) =>
              setProduct((prev) => ({
                ...prev,
                quantity: Number(e.target.value),
              }))
            }
            className="w-full rounded-lg border p-3"
          />
        </div>

        <div>
          <label className="mb-2 block text-sm font-medium">
            Low cost_price Alert
          </label>

          <input
            type="number"
            value={product.lowcost_priceAlert}
            onChange={(e) =>
              setProduct((prev) => ({
                ...prev,
                lowcost_priceAlert: Number(e.target.value),
              }))
            }
            className="w-full rounded-lg border p-3"
          />
        </div>

      </div>
    </div>
  );
}