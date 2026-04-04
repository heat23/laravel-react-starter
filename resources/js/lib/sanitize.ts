import DOMPurify from 'dompurify';

/**
 * Sanitize HTML content using DOMPurify.
 * Use this whenever setting dangerouslySetInnerHTML to prevent XSS.
 */
export function sanitizeHtml(content: string): string {
  return DOMPurify.sanitize(content);
}
