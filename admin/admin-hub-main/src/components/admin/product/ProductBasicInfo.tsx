import { useEffect, useState } from "react";
import type { Product } from "@/types/product";

interface Brand {
    id: number;
    name: string;
}

interface Category {
    id: number;
    name: string;
}

interface Subcategory {
    id: number;
    name: string;
}

export default function ProductBasicInfo() {
    const API = import.meta.env.VITE_ADMIN_API_URL;

    const [brands, setBrands] = useState<Brand[]>([]);
    const [categories, setCategories] = useState<Category[]>([]);
    const [subcategories, setSubcategories] = useState<Subcategory[]>([]);


    interface Props {
        product: Product;
        setProduct: React.Dispatch<React.SetStateAction<Product>>;
    }

    export default function ProductBasicInfo({
        product,
        setProduct,
    }: Props) {
        const [subcategoryId, setSubcategoryId] = useState("");

        useEffect(() => {
            loadBrands();
            loadCategories();
        }, []);

        useEffect(() => {
            if (name.length > 0) {

                const generatedSlug = name
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, "-")
                    .replace(/^-|-$/g, "");

                setSlug(generatedSlug);

                const generatedSku =
                    "ALL-" +
                    Math.random()
                        .toString(36)
                        .substring(2, 8)
                        .toUpperCase();

                setSku(generatedSku);

            }
        }, [name]);

        useEffect(() => {
            if (categoryId) {
                loadSubcategories(categoryId);
            }
        }, [categoryId]);

        async function loadBrands() {

            const res = await fetch(`${API}/brands`);

            const data = await res.json();

            setBrands(data.data || []);

        }

        async function loadCategories() {

            const res = await fetch(`${API}/categories`);

            const data = await res.json();

            setCategories(data.data || []);

        }

        async function loadSubcategories(category: string) {

            const res = await fetch(
                `${API}/subcategories?category_id=${category}`
            );

            const data = await res.json();

            setSubcategories(data.data || []);

        }

        return (
            <div className="rounded-xl border bg-white p-6 shadow-sm">

                <h2 className="mb-6 text-xl font-semibold">

                    Basic Information

                </h2>

                <div className="grid gap-5 md:grid-cols-2">

                    <div>

                        <label className="mb-2 block text-sm font-medium">

                            Product Name

                        </label>

                        <input
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                            className="w-full rounded-lg border p-3"
                            placeholder="Oversized T-Shirt"
                        />

                    </div>

                    <div>

                        <label className="mb-2 block text-sm font-medium">

                            SKU

                        </label>

                        <input
                            value={sku}
                            readOnly
                            className="w-full rounded-lg border bg-gray-100 p-3"
                        />

                    </div>

                    <div>

                        <label className="mb-2 block text-sm font-medium">

                            Slug

                        </label>

                        <input
                            value={slug}
                            readOnly
                            className="w-full rounded-lg border bg-gray-100 p-3"
                        />

                    </div>

                    <div>

                        <label className="mb-2 block text-sm font-medium">

                            Brand

                        </label>

                        <select
                            value={brandId}
                            onChange={(e) => setBrandId(e.target.value)}
                            className="w-full rounded-lg border p-3"
                        >

                            <option value="">

                                Select Brand

                            </option>

                            {brands.map((brand) => (

                                <option
                                    key={brand.id}
                                    value={brand.id}
                                >
                                    {brand.name}
                                </option>

                            ))}

                        </select>

                    </div>

                    <div>

                        <label className="mb-2 block text-sm font-medium">

                            Category

                        </label>

                        <select
                            value={categoryId}
                            onChange={(e) => setCategoryId(e.target.value)}
                            className="w-full rounded-lg border p-3"
                        >

                            <option value="">

                                Select Category

                            </option>

                            {categories.map((category) => (

                                <option
                                    key={category.id}
                                    value={category.id}
                                >
                                    {category.name}
                                </option>

                            ))}

                        </select>

                    </div>

                    <div>

                        <label className="mb-2 block text-sm font-medium">

                            Subcategory

                        </label>

                        <select
                            value={subcategoryId}
                            onChange={(e) => setSubcategoryId(e.target.value)}
                            className="w-full rounded-lg border p-3"
                        >

                            <option value="">

                                Select Subcategory

                            </option>

                            {subcategories.map((subcategory) => (

                                <option
                                    key={subcategory.id}
                                    value={subcategory.id}
                                >
                                    {subcategory.name}
                                </option>

                            ))}

                        </select>

                    </div>

                </div>

            </div>
        );
    }