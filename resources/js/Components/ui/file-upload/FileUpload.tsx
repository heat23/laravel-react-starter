import { Upload } from "lucide-react";
import { toast } from "sonner";

import { useCallback, useRef, useState } from "react";

import { formatFileSize } from "@/lib/format";
import { cn } from "@/lib/utils";

import { FilePreview } from "./FilePreview";

interface FileUploadProps {
  accept?: string;
  maxSize?: number;
  maxFiles?: number;
  multiple?: boolean;
  onFilesSelected: (files: File[]) => void;
  onFileRemoved?: (file: File) => void;
  disabled?: boolean;
  className?: string;
  children?: React.ReactNode;
  progress?: number | null;
}

export function FileUpload({
  accept,
  maxSize = 10 * 1024 * 1024,
  maxFiles = 1,
  multiple = false,
  onFilesSelected,
  onFileRemoved,
  disabled = false,
  className,
  children,
  progress = null,
}: FileUploadProps) {
  const [files, setFiles] = useState<File[]>([]);
  const [errors, setErrors] = useState<string[]>([]);
  const [isDragOver, setIsDragOver] = useState(false);
  const inputRef = useRef<HTMLInputElement>(null);

  const validateFiles = useCallback(
    (incoming: File[]): { valid: File[]; errors: string[] } => {
      const newErrors: string[] = [];
      const valid: File[] = [];

      const remainingSlots = maxFiles - files.length;
      const toProcess = multiple ? incoming.slice(0, remainingSlots) : [incoming[0]];

      if (incoming.length > remainingSlots && multiple) {
        newErrors.push(`Maximum ${maxFiles} file${maxFiles > 1 ? "s" : ""} allowed`);
      }

      for (const file of toProcess) {
        if (!file) continue;

        if (file.size > maxSize) {
          newErrors.push(
            `"${file.name}" is too large (max ${formatFileSize(maxSize)})`,
          );
          continue;
        }

        if (accept) {
          const acceptedTypes = accept.split(",").map((t) => t.trim());
          const fileType = file.type;
          const fileExt = `.${file.name.split(".").pop()?.toLowerCase()}`;

          const isAccepted = acceptedTypes.some((type) => {
            if (type.endsWith("/*")) {
              return fileType.startsWith(type.replace("/*", "/"));
            }
            if (type.startsWith(".")) {
              return fileExt === type.toLowerCase();
            }
            return fileType === type;
          });

          if (!isAccepted) {
            newErrors.push(`"${file.name}" is not an accepted file type`);
            continue;
          }
        }

        valid.push(file);
      }

      return { valid, errors: newErrors };
    },
    [accept, maxSize, maxFiles, multiple, files.length],
  );

  function handleFiles(incoming: FileList | null) {
    if (!incoming || incoming.length === 0) return;

    setErrors([]);
    const fileArray = Array.from(incoming);
    const { valid, errors: newErrors } = validateFiles(fileArray);

    if (newErrors.length > 0) {
      setErrors(newErrors);
    }

    if (valid.length > 0) {
      const updated = multiple ? [...files, ...valid] : valid;
      setFiles(updated);
      onFilesSelected(updated);
      toast.success(
        `${valid.length} file${valid.length > 1 ? "s" : ""} added`,
      );
    }
  }

  function handleRemove(index: number) {
    const removed = files[index];
    const updated = files.filter((_, i) => i !== index);
    setFiles(updated);
    setErrors([]);
    onFileRemoved?.(removed);
    onFilesSelected(updated);
  }

  function handleDragOver(e: React.DragEvent) {
    e.preventDefault();
    if (!disabled) setIsDragOver(true);
  }

  function handleDragLeave(e: React.DragEvent) {
    e.preventDefault();
    setIsDragOver(false);
  }

  function handleDrop(e: React.DragEvent) {
    e.preventDefault();
    setIsDragOver(false);
    if (disabled) return;
    handleFiles(e.dataTransfer.files);
  }

  return (
    <div className={cn("space-y-3", className)}>
      <div
        role="button"
        tabIndex={disabled ? -1 : 0}
        aria-label="Upload files"
        aria-disabled={disabled}
        onClick={() => !disabled && inputRef.current?.click()}
        onKeyDown={(e) => {
          if ((e.key === "Enter" || e.key === " ") && !disabled) {
            e.preventDefault();
            inputRef.current?.click();
          }
        }}
        onDragOver={handleDragOver}
        onDragLeave={handleDragLeave}
        onDrop={handleDrop}
        className={cn(
          "flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed p-6 transition-colors",
          isDragOver
            ? "border-primary bg-primary/5"
            : "border-muted-foreground/25 hover:border-muted-foreground/50",
          disabled && "cursor-not-allowed opacity-50",
        )}
      >
        {children ?? (
          <>
            <Upload className="mb-2 h-8 w-8 text-muted-foreground" />
            <p className="text-sm font-medium">
              Drag & drop or click to upload
            </p>
            <p className="mt-1 text-xs text-muted-foreground">
              {accept
                ? `Accepted: ${accept}`
                : "Any file type"}
              {` (max ${formatFileSize(maxSize)})`}
            </p>
          </>
        )}
        <input
          ref={inputRef}
          type="file"
          accept={accept}
          multiple={multiple}
          disabled={disabled}
          onChange={(e) => handleFiles(e.target.files)}
          className="hidden"
          aria-hidden="true"
        />
      </div>

      {progress != null && progress >= 0 && progress < 100 && (
        <div className="space-y-1">
          <div className="flex items-center justify-between text-xs text-muted-foreground">
            <span>Uploading...</span>
            <span>{Math.round(progress)}%</span>
          </div>
          <progress
            className="h-2 w-full appearance-none overflow-hidden rounded-full [&::-moz-progress-bar]:rounded-full [&::-moz-progress-bar]:bg-primary [&::-webkit-progress-bar]:rounded-full [&::-webkit-progress-bar]:bg-muted [&::-webkit-progress-value]:rounded-full [&::-webkit-progress-value]:bg-primary [&::-webkit-progress-value]:transition-all"
            value={Math.round(progress)}
            max={100}
            aria-label="Upload progress"
          />
        </div>
      )}

      {errors.length > 0 && (
        <div role="alert" className="space-y-1">
          {errors.map((error, i) => (
            <p key={i} className="text-sm text-destructive">
              {error}
            </p>
          ))}
        </div>
      )}

      {files.length > 0 && (
        <div className="space-y-2">
          {files.map((file, i) => (
            <FilePreview
              key={`${file.name}-${file.size}-${file.lastModified}-${i}`}
              file={file}
              onRemove={() => handleRemove(i)}
            />
          ))}
        </div>
      )}
    </div>
  );
}
