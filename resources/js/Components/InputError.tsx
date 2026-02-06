import { cn } from "@/lib/utils";

interface InputErrorProps {
  message?: string;
  className?: string;
  id?: string;
}

export default function InputError({ message, className = "", id }: InputErrorProps) {
  return message ? (
    <p id={id} className={cn("text-sm text-destructive", className)}>{message}</p>
  ) : null;
}
