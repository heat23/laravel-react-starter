import { ArrowLeft } from 'lucide-react';

import { Head, Link } from '@inertiajs/react';

import { PrivacyContent } from '@/Components/legal/LegalContent';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

export default function Privacy() {
  return (
    <>
      <Head title="Privacy Policy">
        <meta
          name="description"
          content="Privacy policy — how we collect, use, store, and protect your personal data."
        />
      </Head>
      <div className="min-h-screen bg-background">
        <div className="container max-w-3xl py-12">
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
              <CardTitle className="text-2xl">Privacy Policy</CardTitle>
            </CardHeader>
            <CardContent>
              <PrivacyContent />
            </CardContent>
          </Card>
        </div>
      </div>
    </>
  );
}
