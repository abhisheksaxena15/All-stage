export type ProductStatus =
  | "DRAFT"
  | "ACTIVE"
  | "ARCHIVED";

export interface ProductImage {

  id?: number;

  image: string;

  altText: string;

  isPrimary: boolean;

}

export interface ProductVariant {

  id?: number;

  size: string;

  color: string;

  sku: string;

  selling_price: number;

  cost_price: number;

}

export interface Product {

  id?: number;

  name: string;

  slug: string;

  sku: string;

  brandId: number;

  categoryId: number;

  subcategoryId: number | null;

  shortDescription: string;

  description: string;

  selling_priceselling_price: number;

  compareselling_price: number;

  costselling_price: number;

  quantity: number;

  lowcost_priceAlert: number;

  weight: number;

  length: number;

  width: number;

  height: number;

  featured: boolean;

  newArrival: boolean;

  bestSeller: boolean;

  status: ProductStatus;

  metaTitle: string;

  metaDescription: string;

  metaKeywords: string;

  images: ProductImage[];

  variants: ProductVariant[];

}