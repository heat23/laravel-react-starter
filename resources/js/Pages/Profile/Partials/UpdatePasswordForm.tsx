import { CheckCircle2 } from 'lucide-react';

import { FormEventHandler, useRef } from 'react';

import { useForm } from '@inertiajs/react';

import InputError from '@/Components/InputError';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { LoadingButton } from '@/Components/ui/loading-button';
import { useUnsavedChanges } from '@/hooks/useUnsavedChanges';

interface UpdatePasswordFormProps {
    className?: string;
}

export default function UpdatePasswordForm({ className = '' }: UpdatePasswordFormProps) {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);

    const {
        data,
        setData,
        errors,
        setError,
        clearErrors,
        put,
        reset,
        processing,
        recentlySuccessful,
        isDirty,
    } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    useUnsavedChanges(isDirty);

    const updatePassword: FormEventHandler = (e) => {
        e.preventDefault();

        clearErrors();
        const missing: Record<string, string> = {};
        if (!data.current_password) {
            missing.current_password = 'Current password is required.';
        }
        if (!data.password) {
            missing.password = 'New password is required.';
        }
        if (!data.password_confirmation) {
            missing.password_confirmation = 'Please confirm your new password.';
        }
        if (Object.keys(missing).length > 0) {
            setError(missing);
            return;
        }

        put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => reset(),
            onError: (errors) => {
                if (errors.password) {
                    reset('password', 'password_confirmation');
                    passwordInput.current?.focus();
                }

                if (errors.current_password) {
                    reset('current_password');
                    currentPasswordInput.current?.focus();
                }
            },
        });
    };

    return (
        <section className={className}>
            <form onSubmit={updatePassword} className="space-y-6">
                <div className="space-y-2">
                    <Label htmlFor="current_password">Current Password</Label>

                    <Input
                        id="current_password"
                        ref={currentPasswordInput}
                        value={data.current_password}
                        onChange={(e) =>
                            setData('current_password', e.target.value)
                        }
                        type="password"
                        autoComplete="current-password"
                        required
                        aria-describedby={errors.current_password ? "password-current-error" : undefined}
                        aria-invalid={!!errors.current_password}
                    />

                    <InputError
                        id="password-current-error"
                        message={errors.current_password}
                    />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="password">New Password</Label>

                    <Input
                        id="password"
                        ref={passwordInput}
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        type="password"
                        autoComplete="new-password"
                        required
                        aria-describedby={errors.password ? "password-new-error" : undefined}
                        aria-invalid={!!errors.password}
                    />

                    <InputError id="password-new-error" message={errors.password} />
                </div>

                <div className="space-y-2">
                    <Label htmlFor="password_confirmation">Confirm Password</Label>

                    <Input
                        id="password_confirmation"
                        value={data.password_confirmation}
                        onChange={(e) =>
                            setData('password_confirmation', e.target.value)
                        }
                        type="password"
                        autoComplete="new-password"
                        required
                        aria-describedby={errors.password_confirmation ? "password-confirm-error" : undefined}
                        aria-invalid={!!errors.password_confirmation}
                    />

                    <InputError
                        id="password-confirm-error"
                        message={errors.password_confirmation}
                    />
                </div>

                <div className="flex items-center gap-4">
                    <LoadingButton
                        loading={processing}
                        loadingText="Updating..."
                        disabled={
                            !data.current_password ||
                            !data.password ||
                            !data.password_confirmation
                        }
                    >
                        Update Password
                    </LoadingButton>

                    {recentlySuccessful && (
                        <p className="text-sm text-success flex items-center gap-1.5 animate-fade-in">
                            <CheckCircle2 className="h-4 w-4" />
                            Changes saved successfully
                        </p>
                    )}
                </div>
            </form>
        </section>
    );
}
