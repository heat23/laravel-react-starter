import { forwardRef, useEffect, useImperativeHandle, useRef, InputHTMLAttributes } from "react";

import { cn } from "@/lib/utils";

interface TextInputProps extends InputHTMLAttributes<HTMLInputElement> {
  isFocused?: boolean;
}

const TextInput = forwardRef<HTMLInputElement, TextInputProps>(
  ({ className = "", isFocused = false, ...props }, ref) => {
    const localRef = useRef<HTMLInputElement>(null);

    useImperativeHandle(ref, () => localRef.current as HTMLInputElement);

    useEffect(() => {
      if (isFocused && localRef.current) {
        localRef.current.focus();
      }
    }, [isFocused]);

    return (
      <input
        {...props}
        ref={localRef}
        className={cn(
          "flex h-11 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background",
          "file:border-0 file:bg-transparent file:text-sm file:font-medium",
          "placeholder:text-muted-foreground",
          "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2",
          "disabled:cursor-not-allowed disabled:opacity-50",
          className
        )}
      />
    );
  }
);

TextInput.displayName = "TextInput";

export default TextInput;
