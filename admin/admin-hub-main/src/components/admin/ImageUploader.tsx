/**
 * Single or multiple image picker with preview & remove.
 * Emits File[] via onChange plus optional existing image URLs to keep.
 */
import { UploadCloud, X, ImageIcon } from "lucide-react";
import { useRef, useState } from "react";
import { cn } from "@/lib/utils";

export interface ImageUploaderProps {
  multiple?: boolean;
  value?: File[];
  onChange: (files: File[]) => void;
  existing?: { id?: string | number; url: string }[];
  onRemoveExisting?: (id: string | number | undefined, index: number) => void;
  maxSizeMB?: number;
  accept?: string;
  label?: string;
  primaryId?: string | number | null;
  primaryIndex?: number | null;
  onSetPrimary?: (type: "existing" | "new", val: string | number) => void;
}

export function ImageUploader({
  multiple,
  value = [],
  onChange,
  existing = [],
  onRemoveExisting,
  maxSizeMB = 5,
  accept = "image/*",
  label,
  primaryId = null,
  primaryIndex = null,
  onSetPrimary,
}: ImageUploaderProps) {
  const inputRef = useRef<HTMLInputElement>(null);
  const [error, setError] = useState<string | null>(null);
  const [drag, setDrag] = useState(false);

  const handleFiles = (files: FileList | null) => {
    if (!files || files.length === 0) return;
    setError(null);
    const arr = Array.from(files);
    for (const f of arr) {
      if (f.size > maxSizeMB * 1024 * 1024) {
        setError(`"${f.name}" exceeds ${maxSizeMB}MB limit`);
        return;
      }
    }
    onChange(multiple ? [...value, ...arr] : arr.slice(0, 1));
  };

  return (
    <div className="space-y-2">
      {label && <label className="text-sm font-medium">{label}</label>}
      <div
        onDragOver={(e) => {
          e.preventDefault();
          setDrag(true);
        }}
        onDragLeave={() => setDrag(false)}
        onDrop={(e) => {
          e.preventDefault();
          setDrag(false);
          handleFiles(e.dataTransfer.files);
        }}
        onClick={() => inputRef.current?.click()}
        className={cn(
          "flex cursor-pointer flex-col items-center justify-center gap-1 rounded-md border-2 border-dashed border-border bg-muted/20 px-4 py-6 text-center transition-colors hover:border-foreground/40",
          drag && "border-foreground/60 bg-muted/40",
        )}
      >
        <UploadCloud className="h-6 w-6 text-muted-foreground" />
        <div className="text-sm">
          <span className="font-medium">Click to upload</span> or drag & drop
        </div>
        <div className="text-xs text-muted-foreground">
          PNG, JPG, WEBP up to {maxSizeMB}MB {multiple && "· multiple allowed"}
        </div>
        <input
          ref={inputRef}
          type="file"
          multiple={multiple}
          accept={accept}
          className="hidden"
          onChange={(e) => handleFiles(e.target.files)}
        />
      </div>

      {error && <p className="text-xs text-red-600 dark:text-red-400">{error}</p>}

      {(existing.length > 0 || value.length > 0) && (
        <div className="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-6">
          {existing.map((img, idx) => (
            <PreviewTile
              key={`e-${img.id ?? idx}`}
              src={img.url}
              onRemove={onRemoveExisting ? () => onRemoveExisting(img.id, idx) : undefined}
              badge="Saved"
              isPrimary={primaryId != null && img.id != null && String(img.id) === String(primaryId)}
              onMakePrimary={onSetPrimary && img.id != null ? () => onSetPrimary("existing", img.id) : undefined}
            />
          ))}
          {value.map((f, idx) => (
            <PreviewTile
              key={`n-${f.name}-${idx}`}
              src={URL.createObjectURL(f)}
              onRemove={() => onChange(value.filter((_, i) => i !== idx))}
              badge="New"
              isPrimary={primaryIndex !== null && idx === primaryIndex}
              onMakePrimary={onSetPrimary ? () => onSetPrimary("new", idx) : undefined}
            />
          ))}
        </div>
      )}
    </div>
  );
}

function PreviewTile({
  src,
  onRemove,
  badge,
  isPrimary,
  onMakePrimary,
}: {
  src: string;
  onRemove?: () => void;
  badge?: string;
  isPrimary?: boolean;
  onMakePrimary?: () => void;
}) {
  return (
    <div
      className={cn(
        "group relative aspect-square overflow-hidden rounded-md border bg-muted transition-all",
        isPrimary ? "border-amber-400 ring-2 ring-amber-400/50" : "border-border",
      )}
    >
      {src ? (
        // eslint-disable-next-line @next/next/no-img-element
        <img src={src} alt="" className="h-full w-full object-cover" />
      ) : (
        <div className="grid h-full place-items-center text-muted-foreground">
          <ImageIcon className="h-6 w-6" />
        </div>
      )}
      {badge && (
        <span className="absolute left-1 top-1 rounded bg-black/60 px-1.5 py-0.5 text-[10px] font-medium text-white">
          {badge}
        </span>
      )}

      {onMakePrimary && (
        <button
          type="button"
          onClick={onMakePrimary}
          className={cn(
            "absolute bottom-1 left-1 grid h-6 w-6 place-items-center rounded-full bg-black/70 transition-all hover:scale-110",
            isPrimary ? "opacity-100 text-amber-400" : "text-white opacity-0 group-hover:opacity-100",
          )}
          title={isPrimary ? "Primary Thumbnail" : "Make Thumbnail"}
        >
          <svg
            className={cn("h-3.5 w-3.5", isPrimary ? "fill-amber-400" : "fill-none")}
            stroke="currentColor"
            viewBox="0 0 24 24"
            strokeWidth="2"
          >
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
          </svg>
        </button>
      )}

      {onRemove && (
        <button
          type="button"
          onClick={onRemove}
          className="absolute right-1 top-1 grid h-6 w-6 place-items-center rounded-full bg-black/70 text-white opacity-0 transition-opacity hover:bg-red-600 group-hover:opacity-100"
          aria-label="Remove image"
        >
          <X className="h-3 w-3" />
        </button>
      )}
    </div>
  );
}

