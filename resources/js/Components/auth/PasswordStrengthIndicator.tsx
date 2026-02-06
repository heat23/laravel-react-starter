import { Check, X } from "lucide-react";

import { Progress } from "@/Components/ui/progress";

interface PasswordRequirement {
  id: string;
  label: string;
  test: (p: string) => boolean;
}

interface PasswordStrengthIndicatorProps {
  password: string;
  passwordRequirements: PasswordRequirement[];
}

export function PasswordStrengthIndicator({ password, passwordRequirements }: PasswordStrengthIndicatorProps) {
  if (password.length === 0) {
    return null;
  }

  const passedRequirements = passwordRequirements.filter((req) => req.test(password)).length;
  const passwordStrength = passwordRequirements.length > 0 ? (passedRequirements / passwordRequirements.length) * 100 : 0;

  return (
    <div className="space-y-3 animate-fade-in">
      <div className="space-y-1.5">
        <div className="flex justify-between text-xs">
          <span className="text-muted-foreground">Password strength</span>
          <span className={`font-medium ${
            passwordStrength <= 25 ? "text-destructive" :
            passwordStrength <= 50 ? "text-warning" :
            passwordStrength <= 75 ? "text-info" : "text-success"
          }`}>
            {passwordStrength <= 25 ? "Weak" :
             passwordStrength <= 50 ? "Fair" :
             passwordStrength <= 75 ? "Good" : "Strong"}
          </span>
        </div>
        <Progress value={passwordStrength} className="h-1.5" />
      </div>

      <div className="grid grid-cols-2 gap-2 text-xs">
        {passwordRequirements.map((req) => {
          const passed = req.test(password);
          return (
            <div
              key={req.id}
              className={`flex items-center gap-1.5 transition-colors ${
                passed ? "text-success" : "text-muted-foreground"
              }`}
            >
              {passed ? (
                <Check className="h-3 w-3" />
              ) : (
                <X className="h-3 w-3" />
              )}
              {req.label}
            </div>
          );
        })}
      </div>
    </div>
  );
}

export type { PasswordRequirement };
