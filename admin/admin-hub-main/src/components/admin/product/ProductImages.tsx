import { useRef } from "react";
import type { Product } from "@/types/product";

interface Props {
  product: Product;
  setProduct: React.Dispatch<React.SetStateAction<Product>>;
}

export default function ProductImages({
  product,
  setProduct,
}: Props) {
  const inputRef = useRef<HTMLInputElement>(null);

  const handleImages = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (!e.target.files) return;

    const files = Array.from(e.target.files);

    const images = files.map((file) => ({
      image: URL.createObjectURL(file),
      altText: file.name,
      isPrimary: false,
    }));

    setProduct((prev) => ({
      ...prev,
      images: [...prev.images, ...images],
    }));
  };

  const removeImage = (index: number) => {
    setProduct((prev) => ({
      ...prev,
      images: prev.images.filter((_, i) => i !== index),
    }));
  };

  return (
    <div className="rounded-xl border bg-white p-6 shadow-sm">

      <h2 className="text-xl font-semibold mb-5">
        Product Images
      </h2>

      <button
        type="button"
        onClick={() => inputRef.current?.click()}
        className="rounded-lg bg-black px-4 py-2 text-white"
      >
        Upload Images
      </button>

      <input
        ref={inputRef}
        hidden
        multiple
        type="file"
        accept="image/*"
        onChange={handleImages}
      />

      <div className="grid grid-cols-2 md:grid-cols-5 gap-4 mt-6">

        {product.images.map((img, index) => (

          <div
            key={index}
            className="border rounded-lg overflow-hidden"
          >

            <img
              src={img.image}
              className="h-40 w-full object-cover"
            />

            <button
              type="button"
              onClick={() => removeImage(index)}
              className="w-full bg-red-600 py-2 text-white"
            >
              Remove
            </button>

          </div>

        ))}

      </div>

    </div>
  );
}