import { adminApi } from "@/lib/admin-api";

export interface ProductPayload {
  brand_id: number;
  category_id: number;
  subcategory_id?: number | null;

  name: string;
  slug: string;
  sku: string;

  short_description?: string;
  description?: string;

  selling_price: number;
  compare_price: number;
  cost_price: number;

  status: string;

  featured: boolean;
  new_arrival: boolean;
  best_seller: boolean;
}

export async function getProducts() {
  return await adminApi.get("/products");
}

export async function createProduct(data: ProductPayload) {
  return await adminApi.post("/products", data);
}

export async function deleteProduct(id: number) {
  return await adminApi.delete(`/products/${id}`);
}