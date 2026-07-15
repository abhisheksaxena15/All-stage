import type { Product } from "@/types/product";
import { createProduct } from "@/lib/product-api";

interface Props {
  product: Product;
  setProduct: React.Dispatch<React.SetStateAction<Product>>;
}

export default function ProductPublish({
  product,
}: Props) {

  async function save() {

    console.log(product);

    await createProduct(product);

    alert("Product Saved");

  }

  return (

    <div className="rounded-xl border bg-white p-6 shadow-sm">

      <h2 className="mb-6 text-xl font-semibold">

        Publish

      </h2>

      <div className="space-y-4">

        <label className="flex gap-2">

          <input
            type="checkbox"
            checked={product.featured}
            readOnly
          />

          Featured Product

        </label>

        <label className="flex gap-2">

          <input
            type="checkbox"
            checked={product.newArrival}
            readOnly
          />

          New Arrival

        </label>

        <label className="flex gap-2">

          <input
            type="checkbox"
            checked={product.bestSeller}
            readOnly
          />

          Best Seller

        </label>

      </div>

      <button
        onClick={save}
        className="mt-8 rounded-lg bg-black px-8 py-3 text-white"
      >
        Save Product
      </button>

    </div>

  );

}