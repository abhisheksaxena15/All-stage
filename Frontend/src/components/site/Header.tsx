import { Link } from "@tanstack/react-router";
import { Search, User, ShoppingBag, Menu, X } from "lucide-react";
import { PRODUCTS, useCategoriesList } from "@/lib/products";
import { useCart } from "@/context/CartContext";
import { useState, useEffect, useRef } from "react";

export function Header() {
  const { count } = useCart();
  const { categories } = useCategoriesList();
  const [isSearchOpen, setIsSearchOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState("");
  const searchInputRef = useRef<HTMLInputElement>(null);

  // Focus input when search overlay is opened
  useEffect(() => {
    if (isSearchOpen) {
      searchInputRef.current?.focus();
      document.body.style.overflow = "hidden"; // Prevent scrolling
    } else {
      document.body.style.overflow = "unset";
    }
    return () => {
      document.body.style.overflow = "unset";
    };
  }, [isSearchOpen]);

  // Handle escape key to close
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === "Escape") {
        setIsSearchOpen(false);
      }
    };
    window.addEventListener("keydown", handleKeyDown);
    return () => window.removeEventListener("keydown", handleKeyDown);
  }, []);

  const filteredProducts = searchQuery.trim()
    ? PRODUCTS.filter((p) =>
        p.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
        p.collection.toLowerCase().includes(searchQuery.toLowerCase()) ||
        p.category.toLowerCase().includes(searchQuery.toLowerCase()) ||
        p.color.toLowerCase().includes(searchQuery.toLowerCase())
      )
    : [];

  return (
    <header className="sticky top-0 z-40 border-b border-ink/10 bg-bone/90 backdrop-blur">
      <div className="mx-auto flex max-w-[1400px] items-center justify-between px-4 py-4 lg:px-8">
        <button className="lg:hidden" aria-label="Menu">
          <Menu className="h-5 w-5" />
        </button>

        <nav className="hidden gap-7 text-[13px] font-medium uppercase tracking-wider lg:flex">
          <Link to="/collections/$handle" params={{ handle: "shop-all" }} className="hover:text-molten">
            New
          </Link>
          <Link to="/collections/$handle" params={{ handle: "shop-all" }} className="text-molten hover:text-ink">
            50% Sale
          </Link>
          {categories.filter(c => c.handle !== "shop-all").map((c) => (
            <Link key={c.handle} to="/collections/$handle" params={{ handle: c.handle }} className="hover:text-molten">
              {c.label}
            </Link>
          ))}
        </nav>

        <Link to="/" className="text-display text-2xl tracking-[0.15em] lg:text-3xl">
          ALL<span className="text-molten">/</span>STAGE
        </Link>

        <div className="flex items-center gap-4">
          <button
            onClick={() => setIsSearchOpen(true)}
            aria-label="Search"
            className="hover:text-molten"
          >
            <Search className="h-5 w-5" />
          </button>
          <Link to="/account" aria-label="Account" className="hidden hover:text-molten sm:block">
            <User className="h-5 w-5" />
          </Link>
          <Link to="/cart" aria-label="Bag" className="relative hover:text-molten">
            <ShoppingBag className="h-5 w-5" />
            <span className="absolute -right-2 -top-2 flex h-4 w-4 items-center justify-center rounded-full bg-molten text-[10px] font-bold text-bone">
              {count}
            </span>
          </Link>
        </div>
      </div>

      {/* Secondary category strip */}
      <div className="hidden border-t border-ink/10 lg:block">
        <div className="mx-auto flex max-w-[1400px] items-center justify-center gap-8 px-4 py-2 text-[11px] font-mono uppercase tracking-widest text-muted-foreground lg:px-8">
          {categories.map((c) => (
            <Link key={c.handle} to="/collections/$handle" params={{ handle: c.handle }} className="hover:text-ink">
              {c.label}
            </Link>
          ))}
          <Link to="/account" className="hover:text-ink">Account</Link>
          <Link to="/cart" className="hover:text-ink">Cart</Link>
        </div>
      </div>

      {/* Search Overlay */}
      {isSearchOpen && (
        <div className="fixed inset-0 z-50 flex flex-col bg-bone/98 p-6 backdrop-blur-md md:p-12 animate-in fade-in duration-200">
          <div className="mx-auto w-full max-w-[800px]">
            {/* Header / Input area */}
            <div className="flex items-center justify-between border-b border-ink/10 pb-4">
              <div className="flex flex-1 items-center gap-3">
                <Search className="h-6 w-6 text-muted-foreground" />
                <input
                  ref={searchInputRef}
                  type="text"
                  placeholder="SEARCH HEAVYWEIGHT TEES, SHIRTS, TANKS..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full bg-transparent text-xl font-medium tracking-wide placeholder-muted-foreground outline-none uppercase text-ink md:text-2xl"
                />
              </div>
              <button
                onClick={() => {
                  setIsSearchOpen(false);
                  setSearchQuery("");
                }}
                className="ml-4 rounded-full p-2 hover:bg-ink/5 hover:text-molten transition-colors"
                aria-label="Close search"
              >
                <X className="h-6 w-6" />
              </button>
            </div>

            {/* Results Area */}
            <div className="mt-8 max-h-[70vh] overflow-y-auto pr-2">
              {searchQuery.trim() === "" ? (
                <div>
                  <h3 className="font-mono text-xs uppercase tracking-widest text-muted-foreground">Popular Searches</h3>
                  <div className="mt-4 flex flex-wrap gap-2">
                    {["Tees", "Oversized", "Tank", "Collar"].map((term) => (
                      <button
                        key={term}
                        onClick={() => setSearchQuery(term)}
                        className="border border-ink/10 px-4 py-2 text-xs font-medium uppercase tracking-wider hover:border-ink hover:bg-ink hover:text-bone transition-all"
                      >
                        {term}
                      </button>
                    ))}
                  </div>
                </div>
              ) : filteredProducts.length > 0 ? (
                <div className="space-y-4">
                  <h3 className="font-mono text-xs uppercase tracking-widest text-muted-foreground">
                    Products ({filteredProducts.length})
                  </h3>
                  <div className="grid gap-4 sm:grid-cols-2">
                    {filteredProducts.map((product) => (
                      <Link
                        key={product.handle}
                        to="/products/$handle"
                        params={{ handle: product.handle }}
                        onClick={() => {
                          setIsSearchOpen(false);
                          setSearchQuery("");
                        }}
                        className="flex gap-4 border border-ink/5 bg-bone p-3 hover:border-ink/20 hover:shadow-sm transition-all"
                      >
                        <div className="h-20 w-16 overflow-hidden bg-muted flex-shrink-0">
                          <img
                            src={product.image}
                            alt={product.altText}
                            className="h-full w-full object-cover object-center"
                          />
                        </div>
                        <div className="flex flex-col justify-between py-1">
                          <div>
                            <div className="text-xs font-mono uppercase text-muted-foreground tracking-wider">
                              {product.collection}
                            </div>
                            <h4 className="text-sm font-semibold text-ink uppercase tracking-wide">
                              {product.title}
                            </h4>
                          </div>
                          <div className="flex items-center gap-2">
                            <span className="text-sm font-bold text-ink">
                              ₹{product.selling_price}
                            </span>
                            <span className="text-[11px] text-muted-foreground line-through">
                              ₹{product.mrp}
                            </span>
                          </div>
                        </div>
                      </Link>
                    ))}
                  </div>
                </div>
              ) : (
                <div className="py-12 text-center">
                  <p className="text-muted-foreground">NO PRODUCTS FOUND FOR "{searchQuery.toUpperCase()}"</p>
                  <p className="mt-2 text-xs text-muted-foreground font-mono">TRY SEARCHING FOR "TEES", "SHIRT", OR "OLIVE"</p>
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </header>
  );
}
