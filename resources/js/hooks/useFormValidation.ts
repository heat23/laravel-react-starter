import { z } from 'zod';

import { useState, useCallback } from 'react';

export function useFormValidation<T extends z.ZodObject<z.ZodRawShape>>(schema: T) {
    const [errors, setErrors] = useState<Partial<Record<keyof z.infer<T>, string>>>({});

    const validateField = useCallback((field: keyof z.infer<T>, value: unknown) => {
        const fieldSchema = schema.shape[field as string];
        if (!fieldSchema) return;

        const result = (fieldSchema as z.ZodTypeAny).safeParse(value);
        setErrors(prev => ({
            ...prev,
            [field]: result.success ? undefined : result.error.errors[0]?.message,
        }));
    }, [schema]);

    const validateAll = useCallback((data: z.infer<T>) => {
        const result = schema.safeParse(data);
        if (!result.success) {
            const fieldErrors: Partial<Record<keyof z.infer<T>, string>> = {};
            result.error.errors.forEach((err) => {
                const field = err.path[0] as keyof z.infer<T>;
                fieldErrors[field] = err.message;
            });
            setErrors(fieldErrors);
            return false;
        }
        setErrors({});
        return true;
    }, [schema]);

    const clearError = useCallback((field: keyof z.infer<T>) => {
        setErrors(prev => ({ ...prev, [field]: undefined }));
    }, []);

    return { errors, validateField, validateAll, clearError, setErrors };
}
