import { useState } from "react";
import ProductBasicInfo from "./ProductBasicInfo";
import ProductPricing from "./ProductPricing";
import ProductInventory from "./ProductInventory";
import ProductDescription from "./ProductDescription";
import ProductImages from "./ProductImages";
import ProductSEO from "./ProductSEO";
import ProductPublish from "./ProductPublish";
import type { Product } from "@/types/product";

export default function ProductForm() {
  const [product, setProduct] = useState<Product>({
    name: "",
    slug: "",
    sku: "",
    brandId: 0,
    categoryId: 0,
    subcategoryId: null,

    shortDescription: "",
    description: "",

    sellingPrice: 0,
    comparePrice: 0,
    costPrice: 0,

    quantity: 0,
    lowStockAlert: 5,

    weight: 0,
    length: 0,
    width: 0,
    height: 0,

    featured: false,
    newArrival: false,
    bestSeller: false,

    status: "DRAFT",

    metaTitle: "",
    metaDescription: "",
    metaKeywords: "",

    images: [],
    variants: []
  });

  return (
    <div className="space-y-6">
      <ProductBasicInfo
        product={product}
        setProduct={setProduct}
      />

      <ProductPricing
        product={product}
        setProduct={setProduct}
      />

      <ProductInventory
        product={product}
        setProduct={setProduct}
      />

      <ProductDescription
        product={product}
        setProduct={setProduct}
      />

      <ProductImages
        product={product}
        setProduct={setProduct}
      />

      <ProductSEO
        product={product}
        setProduct={setProduct}
      />

      <ProductPublish
        product={product}
        setProduct={setProduct}
      />
    </div>
  );
}