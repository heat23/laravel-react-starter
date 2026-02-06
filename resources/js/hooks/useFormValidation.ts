import { z } from 'zod';

import { useState, useCallback, useMemo } from 'react';

type SchemaWithShape = z.ZodObject<z.ZodRawShape> | z.ZodEffects<z.ZodObject<z.ZodRawShape>>;

function getShape(schema: SchemaWithShape): z.ZodRawShape {
    if (schema instanceof z.ZodEffects) {
        return (schema._def.schema as z.ZodObject<z.ZodRawShape>).shape;
    }
    return schema.shape;
}

export function useFormValidation<T extends SchemaWithShape>(schema: T) {
    const [errors, setErrors] = useState<Partial<Record<keyof z.infer<T>, string>>>({});
    const shape = useMemo(() => getShape(schema), [schema]);

    const validateField = useCallback((field: keyof z.infer<T>, value: unknown) => {
        const fieldSchema = shape[field as string];
        if (!fieldSchema) return;

        const result = (fieldSchema as z.ZodTypeAny).safeParse(value);
        setErrors(prev => ({
            ...prev,
            [field]: result.success ? undefined : result.error.errors[0]?.message,
        }));
    }, [shape]);

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
