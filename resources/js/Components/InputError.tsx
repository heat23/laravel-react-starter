import { cn } from "@/lib/utils";

interface InputErrorProps {
  message?: string;
  className?: string;
}

export default function InputError({ message, className = "" }: InputErrorProps) {
  return message ? (
    <p className={cn("text-sm text-destructive", className)}>{message}</p>
  ) : null;
}
