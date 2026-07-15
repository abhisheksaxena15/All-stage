import type { Product } from "@/types/product";

interface Props {
  product: Product;
  setProduct: React.Dispatch<React.SetStateAction<Product>>;
}

export default function ProductDescription({
  product,
  setProduct,
}: Props) {
  return (
    <div className="rounded-xl border bg-white p-6 shadow-sm">

      <h2 className="mb-6 text-xl font-semibold">
        Description
      </h2>

      <div className="space-y-5">

        <div>

          <label className="mb-2 block text-sm font-medium">
            Short Description
          </label>

          <textarea
            rows={3}
            value={product.shortDescription}
            onChange={(e) =>
              setProduct((prev) => ({
                ...prev,
                shortDescription: e.target.value,
              }))
            }
            className="w-full rounded-lg border p-3"
          />

        </div>

        <div>

          <label className="mb-2 block text-sm font-medium">
            Product Description
          </label>

          <textarea
            rows={8}
            value={product.description}
            onChange={(e) =>
              setProduct((prev) => ({
                ...prev,
                description: e.target.value,
              }))
            }
            className="w-full rounded-lg border p-3"
          />

        </div>

      </div>
    </div>
  );
}