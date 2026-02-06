import { renderHook, act } from '@testing-library/react';
import { describe, it, expect } from 'vitest';
import { z } from 'zod';

import { useFormValidation } from './useFormValidation';

const testSchema = z.object({
  name: z.string().min(1, 'Name is required').max(10, 'Name is too long'),
  email: z.string().email('Invalid email'),
});

describe('useFormValidation', () => {
  // ============================================
  // Initial state
  // ============================================

  describe('initial state', () => {
    it('starts with no errors', () => {
      const { result } = renderHook(() => useFormValidation(testSchema));

      expect(result.current.errors).toEqual({});
    });

    it('returns all expected functions', () => {
      const { result } = renderHook(() => useFormValidation(testSchema));

      expect(typeof result.current.validateField).toBe('function');
      expect(typeof result.current.validateAll).toBe('function');
      expect(typeof result.current.clearError).toBe('function');
      expect(typeof result.current.setErrors).toBe('function');
    });
  });

  // ============================================
  // validateField
  // ============================================

  describe('validateField', () => {
    it('sets error for invalid field', () => {
      const { result } = renderHook(() => useFormValidation(testSchema));

      act(() => {
        result.current.validateField('name', '');
      });

      expect(result.current.errors.name).toBe('Name is required');
    });

    it('clears error for valid field', () => {
      const { result } = renderHook(() => useFormValidation(testSchema));

      act(() => {
        result.current.validateField('name', '');
      });
      expect(result.current.errors.name).toBeDefined();

      act(() => {
        result.current.validateField('name', 'Alice');
      });
      expect(result.current.errors.name).toBeUndefined();
    });

    it('validates email format', () => {
      const { result } = renderHook(() => useFormValidation(testSchema));

      act(() => {
        result.current.validateField('email', 'not-an-email');
      });

      expect(result.current.errors.email).toBe('Invalid email');
    });

    it('does not affect other field errors', () => {
      const { result } = renderHook(() => useFormValidation(testSchema));

      act(() => {
        result.current.validateField('name', '');
        result.current.validateField('email', 'bad');
      });

      expect(result.current.errors.name).toBe('Name is required');
      expect(result.current.errors.email).toBe('Invalid email');

      act(() => {
        result.current.validateField('email', 'good@test.com');
      });

      expect(result.current.errors.name).toBe('Name is required');
      expect(result.current.errors.email).toBeUndefined();
    });
  });

  // ============================================
  // validateAll
  // ============================================

  describe('validateAll', () => {
    it('returns true for valid data', () => {
      const { result } = renderHook(() => useFormValidation(testSchema));

      let isValid: boolean;
      act(() => {
        isValid = result.current.validateAll({ name: 'Alice', email: 'a@b.com' });
      });

      expect(isValid!).toBe(true);
      expect(result.current.errors).toEqual({});
    });

    it('returns false and sets errors for invalid data', () => {
      const { result } = renderHook(() => useFormValidation(testSchema));

      let isValid: boolean;
      act(() => {
        isValid = result.current.validateAll({ name: '', email: 'bad' });
      });

      expect(isValid!).toBe(false);
      expect(result.current.errors.name).toBe('Name is required');
      expect(result.current.errors.email).toBe('Invalid email');
    });

    it('clears previous errors on valid submission', () => {
      const { result } = renderHook(() => useFormValidation(testSchema));

      act(() => {
        result.current.validateAll({ name: '', email: 'bad' });
      });
      expect(result.current.errors.name).toBeDefined();

      act(() => {
        result.current.validateAll({ name: 'Alice', email: 'a@b.com' });
      });
      expect(result.current.errors).toEqual({});
    });

    it('shows max length error', () => {
      const { result } = renderHook(() => useFormValidation(testSchema));

      let isValid: boolean;
      act(() => {
        isValid = result.current.validateAll({ name: 'A very long name', email: 'a@b.com' });
      });

      expect(isValid!).toBe(false);
      expect(result.current.errors.name).toBe('Name is too long');
    });
  });

  // ============================================
  // clearError
  // ============================================

  describe('clearError', () => {
    it('clears a specific field error', () => {
      const { result } = renderHook(() => useFormValidation(testSchema));

      act(() => {
        result.current.validateAll({ name: '', email: 'bad' });
      });
      expect(result.current.errors.name).toBeDefined();
      expect(result.current.errors.email).toBeDefined();

      act(() => {
        result.current.clearError('name');
      });

      expect(result.current.errors.name).toBeUndefined();
      expect(result.current.errors.email).toBeDefined();
    });
  });
});
