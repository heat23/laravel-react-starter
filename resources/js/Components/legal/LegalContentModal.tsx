import { Button } from "@/Components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from "@/Components/ui/dialog";

interface LegalContentModalProps {
  type: "terms" | "privacy" | null;
  onClose: () => void;
}

export function LegalContentModal({ type, onClose }: LegalContentModalProps) {
  if (!type) return null;

  const title = type === "terms" ? "Terms of Service" : "Privacy Policy";
  const appName = import.meta.env.VITE_APP_NAME || "Our Application";

  return (
    <Dialog open={!!type} onOpenChange={() => onClose()}>
      <DialogContent className="max-w-2xl max-h-[80vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>{title}</DialogTitle>
        </DialogHeader>

        <div className="prose prose-sm dark:prose-invert max-w-none">
          {type === "terms" ? (
            <div className="space-y-4 text-sm text-muted-foreground">
              <p>
                Welcome to {appName}. By using our service, you agree to these terms.
              </p>

              <h3 className="text-foreground font-semibold">1. Acceptance of Terms</h3>
              <p>
                By accessing or using {appName}, you agree to be bound by these Terms of Service
                and all applicable laws and regulations.
              </p>

              <h3 className="text-foreground font-semibold">2. Use of Service</h3>
              <p>
                You may use our service only for lawful purposes and in accordance with these Terms.
                You agree not to use the service in any way that violates any applicable law or regulation.
              </p>

              <h3 className="text-foreground font-semibold">3. User Accounts</h3>
              <p>
                You are responsible for safeguarding the password that you use to access the service
                and for any activities or actions under your password.
              </p>

              <h3 className="text-foreground font-semibold">4. Termination</h3>
              <p>
                We may terminate or suspend your account immediately, without prior notice or liability,
                for any reason whatsoever, including without limitation if you breach the Terms.
              </p>

              <h3 className="text-foreground font-semibold">5. Limitation of Liability</h3>
              <p>
                In no event shall {appName}, nor its directors, employees, partners, agents, suppliers,
                or affiliates, be liable for any indirect, incidental, special, consequential or punitive damages.
              </p>

              <p className="text-xs text-muted-foreground mt-6">
                These terms are a template. Please customize them for your specific use case
                and consult with a legal professional.
              </p>
            </div>
          ) : (
            <div className="space-y-4 text-sm text-muted-foreground">
              <p>
                This Privacy Policy describes how {appName} collects, uses, and shares your personal information.
              </p>

              <h3 className="text-foreground font-semibold">1. Information We Collect</h3>
              <p>
                We collect information you provide directly to us, such as when you create an account,
                use our services, or contact us for support.
              </p>

              <h3 className="text-foreground font-semibold">2. How We Use Information</h3>
              <p>
                We use the information we collect to provide, maintain, and improve our services,
                to communicate with you, and to personalize your experience.
              </p>

              <h3 className="text-foreground font-semibold">3. Information Sharing</h3>
              <p>
                We do not share your personal information with third parties except as described
                in this policy or with your consent.
              </p>

              <h3 className="text-foreground font-semibold">4. Data Security</h3>
              <p>
                We take reasonable measures to help protect your personal information from loss,
                theft, misuse, and unauthorized access.
              </p>

              <h3 className="text-foreground font-semibold">5. Your Rights</h3>
              <p>
                You may access, update, or delete your account information at any time by logging
                into your account settings.
              </p>

              <p className="text-xs text-muted-foreground mt-6">
                This privacy policy is a template. Please customize it for your specific use case
                and ensure compliance with applicable privacy laws (GDPR, CCPA, etc.).
              </p>
            </div>
          )}
        </div>

        <div className="flex justify-end mt-4">
          <Button variant="outline" onClick={onClose}>
            Close
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  );
}
