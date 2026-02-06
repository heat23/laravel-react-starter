import { File as FileIcon, Image, X } from "lucide-react";

import { useEffect, useRef } from "react";

import { Button } from "@/Components/ui/button";
import { formatFileSize } from "@/lib/format";

interface FilePreviewProps {
  file: File;
  onRemove: () => void;
}

export function FilePreview({ file, onRemove }: FilePreviewProps) {
  const isImage = file.type.startsWith("image/");
  const previewUrlRef = useRef<string | null>(null);
  if (isImage && !previewUrlRef.current) {
    previewUrlRef.current = URL.createObjectURL(file);
  }
  const previewUrl = previewUrlRef.current;

  useEffect(() => {
    return () => {
      if (previewUrlRef.current) {
        URL.revokeObjectURL(previewUrlRef.current);
      }
    };
  }, []);

  return (
    <div className="flex items-center gap-3 rounded-md border bg-card p-2">
      {isImage && previewUrl ? (
        <img
          src={previewUrl}
          alt={file.name}
          className="h-10 w-10 shrink-0 rounded object-cover"
        />
      ) : (
        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded bg-muted">
          {isImage ? (
            <Image className="h-5 w-5 text-muted-foreground" aria-hidden="true" />
          ) : (
            <FileIcon className="h-5 w-5 text-muted-foreground" aria-hidden="true" />
          )}
        </div>
      )}
      <div className="min-w-0 flex-1">
        <p className="truncate text-sm font-medium">{file.name}</p>
        <p className="text-xs text-muted-foreground">
          {formatFileSize(file.size)}
        </p>
      </div>
      <Button
        type="button"
        variant="ghost"
        size="icon"
        className="h-8 w-8 min-h-11 min-w-11 shrink-0"
        onClick={onRemove}
        aria-label={`Remove ${file.name}`}
      >
        <X className="h-4 w-4" />
      </Button>
    </div>
  );
}
