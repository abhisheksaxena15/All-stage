import giraffe from "@/assets/product-giraffe.jpg";
import badIdeas from "@/assets/product-bad-ideas.jpg";
import blaze from "@/assets/product-blaze.jpg";
import olive from "@/assets/product-olive.jpg";
import shirt from "@/assets/product-shirt.jpg";
import tank from "@/assets/product-tank.jpg";
import walnut from "@/assets/product-walnut.jpg";

export type ProductCategory = "Tees" | "Shirts" | "Tanks";

export type Product = {
  handle: string;
  title: string;
  collection: string;
  category: ProductCategory;
  selling_price: number;
  mrp: number;
  image: string;
  altText: string;
  /** Size variants — e.g. "S", "M", "L", "XL" */
  sizes: string[];
  color: string;
  /** Fabric composition */
  fabric: string;
  /** Fabric weight in GSM */
  gsm: number;
  /** Fit / cut */
  fit: string;
  rating: number;
  reviewCount: number;
  badge?: string;
  images?: string[];
  description?: string;
  quantity?: number;
  low_stock_threshold?: number;
};

export const PRODUCTS: Product[] = [
  {
    handle: "giraffe-oversized-tee",
    title: "Giraffe Oversized Tee",
    collection: "Wild Type",
    category: "Tees",
    selling_price: 899,
    mrp: 1799,
    image: giraffe,
    altText: "Bone-coloured oversized tee with a large hand-drawn giraffe graphic on the chest",
    sizes: ["S", "M", "L", "XL", "XXL"],
    color: "Bone",
    fabric: "100% Combed Cotton · Bio-washed",
    gsm: 240,
    fit: "Oversized · Drop-shoulder",
    rating: 4.8,
    reviewCount: 142,
    badge: "BESTSELLER",
  },
  {
    handle: "bad-ideas-tee",
    title: "Bad Ideas Graphic Tee",
    collection: "Ink Series",
    category: "Tees",
    selling_price: 999,
    mrp: 1999,
    image: badIdeas,
    altText: "Black heavyweight tee with white 'Bad Ideas' typography print",
    sizes: ["S", "M", "L", "XL"],
    color: "Ink Black",
    fabric: "100% Ring-spun Cotton",
    gsm: 260,
    fit: "Boxy · Structured hem",
    rating: 4.9,
    reviewCount: 216,
    badge: "NEW",
  },
  {
    handle: "olive-heavy-tee",
    title: "Olive Heavyweight Tee",
    collection: "Base Layer",
    category: "Tees",
    selling_price: 849,
    mrp: 1699,
    image: olive,
    altText: "Olive green heavyweight cotton tee with ribbed collar",
    sizes: ["S", "M", "L", "XL", "XXL"],
    color: "Olive",
    fabric: "100% Combed Cotton",
    gsm: 250,
    fit: "Relaxed · Regular length",
    rating: 4.7,
    reviewCount: 98,
  },
  {
    handle: "camp-collar-shirt",
    title: "Camp Collar Half-Sleeve Shirt",
    collection: "Summer Cut",
    category: "Shirts",
    selling_price: 1499,
    mrp: 2999,
    image: shirt,
    altText: "Cream camp-collar half-sleeve shirt with wooden buttons",
    sizes: ["S", "M", "L", "XL"],
    color: "Bone Cream",
    fabric: "Cotton-Linen Blend",
    gsm: 180,
    fit: "Boxy · Camp collar",
    rating: 4.6,
    reviewCount: 71,
  },
  {
    handle: "walnut-overshirt",
    title: "Walnut Cord Overshirt",
    collection: "Winter Drop",
    category: "Shirts",
    selling_price: 2499,
    mrp: 4999,
    image: walnut,
    altText: "Walnut brown corduroy overshirt with chest pockets",
    sizes: ["M", "L", "XL"],
    color: "Walnut Brown",
    fabric: "Heavyweight Cotton Corduroy",
    gsm: 320,
    fit: "Boxy · Layerable",
    rating: 4.9,
    reviewCount: 54,
    badge: "LOW cost_price",
  },
  {
    handle: "blaze-racer-tank",
    title: "Blaze Racer Tank",
    collection: "Molten Drop",
    category: "Tanks",
    selling_price: 699,
    mrp: 1399,
    image: blaze,
    altText: "Molten red racer-back tank with raw-cut armholes",
    sizes: ["S", "M", "L", "XL"],
    color: "Molten Red",
    fabric: "Cotton-Modal Rib",
    gsm: 200,
    fit: "Slim · Racer-back",
    rating: 4.7,
    reviewCount: 63,
  },
  {
    handle: "essential-rib-tank",
    title: "Essential Rib Tank",
    collection: "Base Layer",
    category: "Tanks",
    selling_price: 599,
    mrp: 1199,
    image: tank,
    altText: "Ink black ribbed cotton tank top",
    sizes: ["S", "M", "L", "XL"],
    color: "Ink Black",
    fabric: "Ribbed Cotton",
    gsm: 210,
    fit: "Regular · Straight hem",
    rating: 4.8,
    reviewCount: 88,
  },
];

import { useState, useEffect } from "react";

export function mapDbProductToStorefront(dbProd: any): Product {
  let imgUrl = dbProd.primary_image_url;
  if (!imgUrl && dbProd.images && dbProd.images.length > 0) {
    imgUrl = dbProd.images[0].url || dbProd.images[0].image_url;
  }
  if (!imgUrl) {
    imgUrl = "https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&q=80&w=1000";
  }

  let images: string[] = [];
  if (dbProd.images && Array.isArray(dbProd.images)) {
    images = dbProd.images.map((img: any) => img.url || img.image_url);
  } else {
    images = [imgUrl];
  }

  return {
    handle: dbProd.slug || `prod-${dbProd.id}`,
    title: dbProd.name || "Untitled Product",
    collection: dbProd.brand_name || "Streetwear",
    category: (dbProd.category_name || "Tees") as ProductCategory,
    selling_price: Number(dbProd.selling_price) || 0,
    mrp: Number(dbProd.compare_price) || Number(dbProd.selling_price) || 0,
    image: imgUrl,
    altText: dbProd.short_description || dbProd.name || "",
    sizes: ["S", "M", "L", "XL"],
    color: "Solid",
    fabric: "100% Cotton",
    gsm: 240,
    fit: "Oversized Fit",
    rating: 4.8,
    reviewCount: 120,
    badge: dbProd.featured ? "FEATURED" : undefined,
    images: images,
    description: dbProd.description || "",
    quantity: dbProd.quantity != null ? Number(dbProd.quantity) : 100,
    low_stock_threshold: dbProd.low_stock_threshold != null ? Number(dbProd.low_stock_threshold) : 10,
  };
}

export function useProductsList() {
  const [products, setProducts] = useState<Product[]>(PRODUCTS);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const apiBase = import.meta.env.VITE_API_URL ?? "http://localhost/all-stage/All-stage/backend/public/api";
    fetch(`${apiBase}/admin/products`)
      .then((res) => res.json())
      .then((json) => {
        if (json.success && json.data && json.data.data) {
          const dbProducts = json.data.data.map(mapDbProductToStorefront);
          const dbHandles = new Set(dbProducts.map((p: any) => p.handle));
          const filteredHardcoded = PRODUCTS.filter((p) => !dbHandles.has(p.handle));
          setProducts([...dbProducts, ...filteredHardcoded]);
        }
      })
      .catch((err) => {
        console.error("Failed to load products from backend:", err);
      })
      .finally(() => {
        setLoading(false);
      });
  }, []);

  return { products, loading };
}

export function useCategoriesList() {
  const [categories, setCategories] = useState<{ handle: string; label: string }[]>(CATEGORIES);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const apiBase = import.meta.env.VITE_API_URL ?? "http://localhost/all-stage/All-stage/backend/public/api";
    fetch(`${apiBase}/admin/categories`)
      .then((res) => res.json())
      .then((json) => {
        if (json.success && Array.isArray(json.data)) {
          const dbCats = json.data.map((c: any) => ({
            handle: c.slug || `cat-${c.id}`,
            label: c.name || "Untitled Category",
          }));
          const shopAll = { handle: "shop-all", label: "Shop All" };
          const dbHandles = new Set(dbCats.map((c: any) => c.handle));
          const filteredHardcoded = CATEGORIES.filter((c) => c.handle !== "shop-all" && !dbHandles.has(c.handle));
          
          setCategories([shopAll, ...dbCats, ...filteredHardcoded]);
        }
      })
      .catch((err) => {
        console.error("Failed to load categories from backend:", err);
      })
      .finally(() => {
        setLoading(false);
      });
  }, []);

  return { categories, loading };
}

export function getProduct(handle: string) {
  return PRODUCTS.find((p) => p.handle === handle);
}

export function getRelated(handle: string, limit = 4) {
  return PRODUCTS.filter((p) => p.handle !== handle).slice(0, limit);
}

export function getByCategory(cat: string) {
  const c = cat.toLowerCase();
  if (c === "all" || c === "shop-all") return PRODUCTS;
  return PRODUCTS.filter((p) => p.category.toLowerCase() === c);
}

export const CATEGORIES = [
  { handle: "shop-all", label: "Shop All" },
  { handle: "tees", label: "Tees" },
  { handle: "shirts", label: "Shirts" },
  { handle: "tanks", label: "Tanks" },
];
