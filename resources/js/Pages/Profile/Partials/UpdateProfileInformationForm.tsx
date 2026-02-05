import { FormEventHandler, useState } from 'react';
import { Transition } from '@headlessui/react';
import { Link, useForm, usePage } from '@inertiajs/react';
import { CheckCircle2 } from 'lucide-react';
import { z } from 'zod';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';

const profileSchema = z.object({
    name: z.string().trim().min(1, "Name is required").max(255, "Name is too long"),
    email: z.string().trim().email("Please enter a valid email address"),
});

interface User {
    name: string;
    email: string;
    email_verified_at: string | null;
}

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
    const [clientErrors, setClientErrors] = useState<{ name?: string; email?: string }>({});

    const { data, setData, patch, errors, processing, recentlySuccessful } =
        useForm({
            name: user.name,
            email: user.email,
        });

    const validateField = (field: 'name' | 'email', value: string) => {
        try {
            profileSchema.shape[field].parse(value);
            setClientErrors(prev => ({ ...prev, [field]: undefined }));
        } catch (e) {
            if (e instanceof z.ZodError) {
                setClientErrors(prev => ({ ...prev, [field]: e.errors[0].message }));
            }
        }
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        // Validate all fields before submit
        const result = profileSchema.safeParse(data);
        if (!result.success) {
            const fieldErrors: { name?: string; email?: string } = {};
            result.error.errors.forEach(err => {
                const field = err.path[0] as 'name' | 'email';
                fieldErrors[field] = err.message;
            });
            setClientErrors(fieldErrors);
            return;
        }

        setClientErrors({});
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
                        onChange={(e) => setData('name', e.target.value)}
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
                        onChange={(e) => setData('email', e.target.value)}
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
