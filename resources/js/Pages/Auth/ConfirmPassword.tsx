import { Lock } from "lucide-react";

import { FormEventHandler } from "react";

import { Link, useForm } from "@inertiajs/react";

import InputError from "@/Components/InputError";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import AuthLayout from "@/Layouts/AuthLayout";

export default function ConfirmPassword() {
  const { data, setData, post, processing, errors, reset } = useForm({
    password: "",
  });

  const submit: FormEventHandler = (e) => {
    e.preventDefault();
    post(route("password.confirm"), {
      onFinish: () => reset("password"),
    });
  };

  return (
    <AuthLayout>
      <div className="space-y-8">
        <div className="text-center lg:text-left">
          <h2 className="text-2xl md:text-3xl font-bold text-foreground">Confirm your password</h2>
          <p className="mt-2 text-muted-foreground">
            This is a secure area of the application. Please confirm your password before continuing.
          </p>
        </div>

        <form onSubmit={submit} className="space-y-5">
          <div className="space-y-2">
            <Label htmlFor="password">Password</Label>
            <div className="relative flex items-center">
              <Lock className="absolute left-3 h-4 w-4 text-muted-foreground" />
              <Input
                id="password"
                type="password"
                name="password"
                value={data.password}
                className="pl-10"
                autoComplete="current-password"
                autoFocus
                onChange={(e) => setData("password", e.target.value)}
                required
              />
            </div>
            <InputError message={errors.password} className="text-xs" />
          </div>

          <Button type="submit" className="w-full" size="lg" disabled={processing}>
            Confirm
          </Button>
        </form>

        <p className="text-center text-sm text-muted-foreground">
          Forgot it?{" "}
          <Link
            href={route("password.request")}
            className="font-medium text-primary hover:text-primary/80 transition-colors"
          >
            Reset your password
          </Link>
        </p>
      </div>
    </AuthLayout>
  );
}
