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

  price: number;

  stock: number;

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

  sellingPrice: number;

  comparePrice: number;

  costPrice: number;

  quantity: number;

  lowStockAlert: number;

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