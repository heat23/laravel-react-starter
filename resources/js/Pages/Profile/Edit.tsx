import { useState } from "react";
import { Head, usePage } from "@inertiajs/react";
import axios from "axios";
import { AlertTriangle } from "lucide-react";
import { toast } from "sonner";
import PageHeader from "@/Components/layout/PageHeader";
import { TimezoneSelector } from "@/Components/settings/TimezoneSelector";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import { Label } from "@/Components/ui/label";
import DashboardLayout from "@/Layouts/DashboardLayout";
import DeleteUserForm from "./Partials/DeleteUserForm";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm";

interface EditProps {
    mustVerifyEmail: boolean;
    status?: string;
    timezone?: string;
}

export default function Edit({ mustVerifyEmail, status, timezone: initialTimezone }: EditProps) {
    const defaultTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const [timezone, setTimezone] = useState(initialTimezone || defaultTimezone);
    const [isSaving, setIsSaving] = useState(false);

    const handleTimezoneChange = async (newTimezone: string) => {
        setTimezone(newTimezone);
        setIsSaving(true);
        try {
            await axios.post("/api/settings", {
                key: "timezone",
                value: newTimezone,
            });
            toast.success("Timezone updated successfully");
        } catch (error) {
            toast.error("Failed to update timezone");
            setTimezone(timezone); // Revert on error
        } finally {
            setIsSaving(false);
        }
    };

    return (
        <DashboardLayout>
            <Head title="Profile" />
            <PageHeader
                title="Profile"
                subtitle="Manage your account details and security settings"
            />
            <div className="container py-8">
                <div className="max-w-3xl mx-auto space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Profile Information</CardTitle>
                            <CardDescription>Update your name, email, and account details.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <UpdateProfileInformationForm
                                mustVerifyEmail={mustVerifyEmail}
                                status={status}
                                className="max-w-xl"
                            />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Preferences</CardTitle>
                            <CardDescription>Customize your display and regional settings.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="max-w-xl space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="timezone">Timezone</Label>
                                    <p className="text-sm text-muted-foreground">
                                        All dates and times will be displayed in your selected timezone.
                                    </p>
                                    <TimezoneSelector
                                        value={timezone}
                                        onChange={handleTimezoneChange}
                                        disabled={isSaving}
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Update Password</CardTitle>
                            <CardDescription>Use a strong password to keep your account secure.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <UpdatePasswordForm className="max-w-xl" />
                        </CardContent>
                    </Card>

                    <Card className="border-destructive/50 bg-destructive/5">
                        <CardHeader>
                            <div className="flex items-center gap-2">
                                <div className="rounded-full bg-destructive/10 p-2">
                                    <AlertTriangle className="h-5 w-5 text-destructive" />
                                </div>
                                <div>
                                    <CardTitle className="text-destructive">Danger Zone</CardTitle>
                                    <CardDescription>
                                        Irreversible actions that will permanently affect your account.
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 rounded-lg border border-destructive/30 bg-background">
                                <div>
                                    <p className="font-medium">Delete Account</p>
                                    <p className="text-sm text-muted-foreground">
                                        Permanently delete your account and all data. This cannot be undone.
                                    </p>
                                </div>
                                <div className="shrink-0">
                                    <DeleteUserForm />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </DashboardLayout>
    );
}
