import { Button } from '@/Components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/Components/ui/dialog';

import { PrivacyContent, TermsContent } from './LegalContent';

interface LegalContentModalProps {
  type: 'terms' | 'privacy' | null;
  onClose: () => void;
}

export function LegalContentModal({ type, onClose }: LegalContentModalProps) {
  if (!type) return null;

  const title = type === 'terms' ? 'Terms of Service' : 'Privacy Policy';

  return (
    <Dialog open={!!type} onOpenChange={() => onClose()}>
      <DialogContent className="max-w-2xl max-h-[80vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>{title}</DialogTitle>
          <DialogDescription>
            {type === 'terms'
              ? 'Review our terms and conditions'
              : 'Our privacy policy and data practices'}
          </DialogDescription>
        </DialogHeader>

        <div className="prose prose-sm dark:prose-invert max-w-none">
          {type === 'terms' ? <TermsContent /> : <PrivacyContent />}
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
