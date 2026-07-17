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
            selling_price selling_price
          </label>

          <input
            type="number"
            value={product.selling_priceselling_price}
            onChange={(e) =>
              setProduct((prev) => ({
                ...prev,
                selling_priceselling_price: Number(e.target.value),
              }))
            }
            className="w-full rounded-lg border p-3"
          />
        </div>

        <div>
          <label className="mb-2 block text-sm font-medium">
            Compare selling_price (MRP)
          </label>

          <input
            type="number"
            value={product.compareselling_price}
            onChange={(e) =>
              setProduct((prev) => ({
                ...prev,
                compareselling_price: Number(e.target.value),
              }))
            }
            className="w-full rounded-lg border p-3"
          />
        </div>

        <div>
          <label className="mb-2 block text-sm font-medium">
            Cost selling_price
          </label>

          <input
            type="number"
            value={product.costselling_price}
            onChange={(e) =>
              setProduct((prev) => ({
                ...prev,
                costselling_price: Number(e.target.value),
              }))
            }
            className="w-full rounded-lg border p-3"
          />
        </div>

      </div>
    </div>
  );
}