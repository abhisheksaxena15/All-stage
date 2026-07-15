import type { Product } from "@/types/product";

interface Props {
  product: Product;
  setProduct: React.Dispatch<React.SetStateAction<Product>>;
}

export default function ProductPricing({
  product,
  setProduct,
}: Props) {
  return (
    <div className="rounded-xl border bg-white p-6 shadow-sm">
      <h2 className="mb-6 text-xl font-semibold">
        Pricing
      </h2>

      <div className="grid gap-5 md:grid-cols-3">

        <div>
          <label className="mb-2 block text-sm font-medium">
            Selling Price
          </label>

          <input
            type="number"
            value={product.sellingPrice}
            onChange={(e) =>
              setProduct((prev) => ({
                ...prev,
                sellingPrice: Number(e.target.value),
              }))
            }
            className="w-full rounded-lg border p-3"
          />
        </div>

        <div>
          <label className="mb-2 block text-sm font-medium">
            Compare Price (MRP)
          </label>

          <input
            type="number"
            value={product.comparePrice}
            onChange={(e) =>
              setProduct((prev) => ({
                ...prev,
                comparePrice: Number(e.target.value),
              }))
            }
            className="w-full rounded-lg border p-3"
          />
        </div>

        <div>
          <label className="mb-2 block text-sm font-medium">
            Cost Price
          </label>

          <input
            type="number"
            value={product.costPrice}
            onChange={(e) =>
              setProduct((prev) => ({
                ...prev,
                costPrice: Number(e.target.value),
              }))
            }
            className="w-full rounded-lg border p-3"
          />
        </div>

      </div>
    </div>
  );
}