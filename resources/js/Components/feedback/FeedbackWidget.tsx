import { MessageSquarePlus } from 'lucide-react';
import { useState } from 'react';

import { router } from '@inertiajs/react';

import { Button } from '@/Components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/Components/ui/dialog';
import { Label } from '@/Components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';

const feedbackTypes = [
  { value: 'bug', label: 'Bug Report' },
  { value: 'feature', label: 'Feature Request' },
  { value: 'general', label: 'General Feedback' },
] as const;

export function FeedbackWidget() {
  const [open, setOpen] = useState(false);
  const [type, setType] = useState('');
  const [message, setMessage] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [submitted, setSubmitted] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setSubmitting(true);
    setErrors({});

    router.post(
      route('feedback.store'),
      { type, message },
      {
        onSuccess: () => {
          setSubmitted(true);
          setType('');
          setMessage('');
          setTimeout(() => {
            setOpen(false);
            setSubmitted(false);
          }, 2000);
        },
        onError: (errs) => {
          setErrors(errs);
        },
        onFinish: () => {
          setSubmitting(false);
        },
        preserveScroll: true,
      }
    );
  }

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button
          variant="default"
          size="icon"
          className="fixed bottom-6 right-6 z-50 h-12 w-12 rounded-full shadow-lg"
          aria-label="Send feedback"
        >
          <MessageSquarePlus className="h-5 w-5" />
        </Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Send Feedback</DialogTitle>
          <DialogDescription>
            Help us improve by sharing your thoughts.
          </DialogDescription>
        </DialogHeader>

        {submitted ? (
          <div
            className="rounded-lg border border-green-200 bg-green-50 p-4 text-center text-sm text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200"
            role="alert"
          >
            Thank you for your feedback!
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="feedback-type">Type</Label>
              <Select value={type} onValueChange={setType}>
                <SelectTrigger
                  id="feedback-type"
                  aria-invalid={!!errors.type}
                  aria-describedby={errors.type ? 'type-error' : undefined}
                >
                  <SelectValue placeholder="What kind of feedback?" />
                </SelectTrigger>
                <SelectContent>
                  {feedbackTypes.map((ft) => (
                    <SelectItem key={ft.value} value={ft.value}>
                      {ft.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
              {errors.type && (
                <p id="type-error" className="text-sm text-destructive">
                  {errors.type}
                </p>
              )}
            </div>

            <div className="space-y-2">
              <Label htmlFor="feedback-message">Message</Label>
              <Textarea
                id="feedback-message"
                rows={4}
                value={message}
                onChange={(e) => setMessage(e.target.value)}
                placeholder="Tell us what's on your mind..."
                aria-invalid={!!errors.message}
                aria-describedby={errors.message ? 'message-error' : undefined}
              />
              {errors.message && (
                <p id="message-error" className="text-sm text-destructive">
                  {errors.message}
                </p>
              )}
            </div>

            <Button type="submit" disabled={submitting} className="w-full">
              {submitting ? 'Sending...' : 'Send Feedback'}
            </Button>
          </form>
        )}
      </DialogContent>
    </Dialog>
  );
}
