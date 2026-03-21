import { AlertTriangle } from 'lucide-react';

import { Alert, AlertDescription, AlertTitle } from '@/Components/ui/alert';

function TemplateDisclaimer() {
  return (
    <Alert variant="destructive" className="mt-6">
      <AlertTriangle className="h-4 w-4" />
      <AlertTitle>Template Content — Do Not Use As-Is</AlertTitle>
      <AlertDescription>
        This is placeholder legal text provided as a starting point. You must
        customize it for your specific use case and have it reviewed by a
        qualified legal professional before publishing.
      </AlertDescription>
    </Alert>
  );
}

interface LegalContentProps {
  appName?: string;
}

export function TermsContent({ appName }: LegalContentProps) {
  const name = appName || import.meta.env.VITE_APP_NAME || 'Our Application';

  return (
    <div className="space-y-4 text-sm text-muted-foreground">
      <p>Welcome to {name}. By using our service, you agree to these terms.</p>

      <h2 className="text-foreground text-lg font-semibold">1. Acceptance of Terms</h2>
      <p>
        By accessing or using {name}, you agree to be bound by these Terms of
        Service and all applicable laws and regulations.
      </p>

      <h2 className="text-foreground text-lg font-semibold">2. Use of Service</h2>
      <p>
        You may use our service only for lawful purposes and in accordance with
        these Terms. You agree not to use the service in any way that violates
        any applicable law or regulation.
      </p>

      <h2 className="text-foreground text-lg font-semibold">3. User Accounts</h2>
      <p>
        You are responsible for safeguarding the password that you use to access
        the service and for any activities or actions under your password.
      </p>

      <h2 className="text-foreground text-lg font-semibold">4. Termination</h2>
      <p>
        We may terminate or suspend your account immediately, without prior
        notice or liability, for any reason whatsoever, including without
        limitation if you breach the Terms.
      </p>

      <h2 className="text-foreground text-lg font-semibold">
        5. Limitation of Liability
      </h2>
      <p>
        In no event shall {name}, nor its directors, employees, partners,
        agents, suppliers, or affiliates, be liable for any indirect,
        incidental, special, consequential or punitive damages.
      </p>

      <TemplateDisclaimer />
    </div>
  );
}

export function PrivacyContent({ appName }: LegalContentProps) {
  const name = appName || import.meta.env.VITE_APP_NAME || 'Our Application';

  return (
    <div className="space-y-4 text-sm text-muted-foreground">
      <p>
        This Privacy Policy describes how {name} collects, uses, and shares your
        personal information.
      </p>

      <h2 className="text-foreground text-lg font-semibold">
        1. Information We Collect
      </h2>
      <p>
        We collect information you provide directly to us, such as when you
        create an account, use our services, or contact us for support.
      </p>

      <h2 className="text-foreground text-lg font-semibold">
        2. How We Use Information
      </h2>
      <p>
        We use the information we collect to provide, maintain, and improve our
        services, to communicate with you, and to personalize your experience.
      </p>

      <h2 className="text-foreground text-lg font-semibold">3. Information Sharing</h2>
      <p>
        We do not share your personal information with third parties except as
        described in this policy or with your consent.
      </p>

      <h2 className="text-foreground text-lg font-semibold">4. Data Security</h2>
      <p>
        We take reasonable measures to help protect your personal information
        from loss, theft, misuse, and unauthorized access.
      </p>

      <h2 className="text-foreground text-lg font-semibold">5. Your Rights</h2>
      <p>
        You may access, update, or delete your account information at any time
        by logging into your account settings.
      </p>

      <TemplateDisclaimer />
    </div>
  );
}
