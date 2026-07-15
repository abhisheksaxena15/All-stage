import type { Product } from "@/types/product";

interface Props {
  product: Product;
  setProduct: React.Dispatch<React.SetStateAction<Product>>;
}

export default function ProductSEO({
  product,
  setProduct,
}: Props) {

  return (

    <div className="rounded-xl border bg-white p-6 shadow-sm">

      <h2 className="text-xl font-semibold mb-6">

        SEO

      </h2>

      <div className="space-y-5">

        <input
          placeholder="Meta Title"
          value={product.metaTitle}
          onChange={(e)=>
            setProduct(prev=>({
              ...prev,
              metaTitle:e.target.value
            }))
          }
          className="w-full rounded-lg border p-3"
        />

        <textarea
          rows={3}
          placeholder="Meta Description"
          value={product.metaDescription}
          onChange={(e)=>
            setProduct(prev=>({
              ...prev,
              metaDescription:e.target.value
            }))
          }
          className="w-full rounded-lg border p-3"
        />

        <input
          placeholder="Keywords"
          value={product.metaKeywords}
          onChange={(e)=>
            setProduct(prev=>({
              ...prev,
              metaKeywords:e.target.value
            }))
          }
          className="w-full rounded-lg border p-3"
        />

      </div>

    </div>

  );

}