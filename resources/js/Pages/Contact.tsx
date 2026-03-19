import { ArrowLeft, Mail } from 'lucide-react';

import { useEffect } from 'react';

import { Head, Link, useForm } from '@inertiajs/react';

import { Button } from '@/Components/ui/button';
import { useAnalytics } from '@/hooks/useAnalytics';
import { AnalyticsEvents } from '@/lib/events';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';

const subjects = [
  'General inquiry',
  'Enterprise pricing',
  'Bug report',
  'Feature request',
] as const;

export default function Contact() {
  const { track } = useAnalytics();
  const { data, setData, post, processing, errors, reset, wasSuccessful } =
    useForm({
      name: '',
      email: '',
      subject: '',
      message: '',
    });

  useEffect(() => {
    track(AnalyticsEvents.ENGAGEMENT_PAGE_VIEWED, { page: 'contact' });
  }, [track]);

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    post(route('contact.store'), {
      onSuccess: () => reset(),
    });
  }

  return (
    <>
      <Head title="Contact Us" />
      <div className="min-h-screen bg-background">
        <div className="container max-w-2xl py-12">
          <div className="mb-6">
            <Button variant="ghost" size="sm" asChild>
              <Link href="/">
                <ArrowLeft className="mr-2 h-4 w-4" />
                Back to Home
              </Link>
            </Button>
          </div>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2 text-2xl">
                <Mail className="h-6 w-6" />
                Contact Us
              </CardTitle>
              <p className="text-sm text-muted-foreground">
                Have a question or need help? Fill out the form below and we'll
                get back to you.
              </p>
            </CardHeader>
            <CardContent>
              {wasSuccessful && (
                <div
                  className="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200"
                  role="alert"
                >
                  Your message has been sent. We'll get back to you soon.
                </div>
              )}

              <form onSubmit={handleSubmit} className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="name">Name</Label>
                  <Input
                    id="name"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    aria-invalid={!!errors.name}
                    aria-describedby={errors.name ? 'name-error' : undefined}
                  />
                  {errors.name && (
                    <p id="name-error" className="text-sm text-destructive">
                      {errors.name}
                    </p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="email">Email</Label>
                  <Input
                    id="email"
                    type="email"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    aria-invalid={!!errors.email}
                    aria-describedby={errors.email ? 'email-error' : undefined}
                  />
                  {errors.email && (
                    <p id="email-error" className="text-sm text-destructive">
                      {errors.email}
                    </p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="subject">Subject</Label>
                  <Select
                    value={data.subject}
                    onValueChange={(value) => setData('subject', value)}
                  >
                    <SelectTrigger
                      id="subject"
                      aria-invalid={!!errors.subject}
                      aria-describedby={
                        errors.subject ? 'subject-error' : undefined
                      }
                    >
                      <SelectValue placeholder="Select a subject" />
                    </SelectTrigger>
                    <SelectContent>
                      {subjects.map((subject) => (
                        <SelectItem key={subject} value={subject}>
                          {subject}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  {errors.subject && (
                    <p id="subject-error" className="text-sm text-destructive">
                      {errors.subject}
                    </p>
                  )}
                </div>

                <div className="space-y-2">
                  <Label htmlFor="message">Message</Label>
                  <Textarea
                    id="message"
                    rows={5}
                    value={data.message}
                    onChange={(e) => setData('message', e.target.value)}
                    aria-invalid={!!errors.message}
                    aria-describedby={
                      errors.message ? 'message-error' : undefined
                    }
                  />
                  <p className="text-xs text-muted-foreground">
                    {data.message.length}/2000 characters
                  </p>
                  {errors.message && (
                    <p id="message-error" className="text-sm text-destructive">
                      {errors.message}
                    </p>
                  )}
                </div>

                <Button type="submit" disabled={processing} className="w-full">
                  {processing ? 'Sending...' : 'Send Message'}
                </Button>
              </form>
            </CardContent>
          </Card>
        </div>
      </div>
    </>
  );
}
