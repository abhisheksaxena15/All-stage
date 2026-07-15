const API = import.meta.env.VITE_ADMIN_API_URL;

export async function createProduct(product: any) {

    const response = await fetch(

        `${API}/products`,

        {

            method: "POST",

            headers: {

                "Content-Type":"application/json"

            },

            body:JSON.stringify(product)

        }

    );

    return response.json();

}

export async function getProducts(){

    const response = await fetch(

        `${API}/products`

    );

    return response.json();

}

// updateProduct()

// deleteProduct()

// uploadImages()

// uploadVariants()