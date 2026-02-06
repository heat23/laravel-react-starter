import { Transition } from '@headlessui/react';
import { CheckCircle2 } from 'lucide-react';
import { z } from 'zod';

import { FormEventHandler } from 'react';

import { Link, useForm, usePage } from '@inertiajs/react';

import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useFormValidation } from '@/hooks/useFormValidation';
import { useUnsavedChanges } from '@/hooks/useUnsavedChanges';
import type { User } from '@/types';

const profileSchema = z.object({
    name: z.string().trim().min(1, "Name is required").max(255, "Name is too long"),
    email: z.string().trim().email("Please enter a valid email address"),
});

interface PageProps {
    auth: {
        user: User;
    };
}

interface UpdateProfileInformationFormProps {
    mustVerifyEmail: boolean;
    status?: string;
    className?: string;
}

export default function UpdateProfileInformationForm({
    mustVerifyEmail,
    status,
    className = '',
}: UpdateProfileInformationFormProps) {
    const user = usePage<PageProps>().props.auth.user;
    const { errors: clientErrors, validateField, validateAll, clearError } = useFormValidation(profileSchema);

    const { data, setData, patch, errors, processing, recentlySuccessful, isDirty } =
        useForm({
            name: user.name,
            email: user.email,
        });

    useUnsavedChanges(isDirty);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        if (!validateAll(data)) return;

        patch(route('profile.update'));
    };

    return (
        <section className={className}>
            <form onSubmit={submit} className="space-y-6">
                <div>
                    <InputLabel htmlFor="name" value="Name" />

                    <TextInput
                        id="name"
                        className="mt-1 block w-full"
                        value={data.name}
                        onChange={(e) => {
                            setData('name', e.target.value);
                            if (clientErrors.name) clearError('name');
                        }}
                        onBlur={(e) => validateField('name', e.target.value)}
                        required
                        isFocused
                        autoComplete="name"
                    />

                    <InputError className="mt-2" message={clientErrors.name || errors.name} />
                </div>

                <div>
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        className="mt-1 block w-full"
                        value={data.email}
                        onChange={(e) => {
                            setData('email', e.target.value);
                            if (clientErrors.email) clearError('email');
                        }}
                        onBlur={(e) => validateField('email', e.target.value)}
                        required
                        autoComplete="username"
                    />

                    <InputError className="mt-2" message={clientErrors.email || errors.email} />
                </div>

                {mustVerifyEmail && user.email_verified_at === null && (
                    <div>
                        <p className="mt-2 text-sm text-foreground">
                            Your email address is unverified.
                            <Link
                                href={route('verification.send')}
                                method="post"
                                as="button"
                                className="rounded-md text-sm text-muted-foreground underline hover:text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            >
                                Click here to re-send the verification email.
                            </Link>
                        </p>

                        {status === 'verification-link-sent' && (
                            <div className="mt-2 text-sm font-medium text-success">
                                A new verification link has been sent to your
                                email address.
                            </div>
                        )}
                    </div>
                )}

                <div className="flex items-center gap-4">
                    <PrimaryButton disabled={processing}>Save Profile</PrimaryButton>

                    <Transition
                        show={recentlySuccessful}
                        enter="transition ease-in-out"
                        enterFrom="opacity-0"
                        leave="transition ease-in-out"
                        leaveTo="opacity-0"
                    >
                        <p className="text-sm text-success flex items-center gap-1.5">
                            <CheckCircle2 className="h-4 w-4" />
                            Changes saved successfully
                        </p>
                    </Transition>
                </div>
            </form>
        </section>
    );
}
