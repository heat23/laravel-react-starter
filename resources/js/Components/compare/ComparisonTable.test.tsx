import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';

import { ComparisonTable } from './ComparisonTable';

const sampleFeatures = [
  { feature: 'Admin panel', us: true, them: false },
  { feature: 'Frontend', us: 'React 18', them: 'Vue 3' },
  { feature: 'Billing', us: true, them: true },
  { feature: 'Feature flags', us: true, them: false },
];

describe('ComparisonTable', () => {
  it('renders correct number of rows from props', () => {
    render(
      <ComparisonTable
        features={sampleFeatures}
        usName="Our Product"
        themName="Competitor"
      />,
    );

    const rows = screen.getAllByRole('row');
    // 1 header row + 4 data rows
    expect(rows).toHaveLength(5);
  });

  it('renders checkmark for true values and dash for false values', () => {
    render(
      <ComparisonTable
        features={sampleFeatures}
        usName="Our Product"
        themName="Competitor"
      />,
    );

    const yesLabels = screen.getAllByLabelText('Yes');
    const noLabels = screen.getAllByLabelText('No');

    // true values: Admin panel (us), Billing (us, them), Feature flags (us) = 4
    expect(yesLabels).toHaveLength(4);
    // false values: Admin panel (them), Feature flags (them) = 2
    expect(noLabels).toHaveLength(2);
  });

  it('renders string values as text', () => {
    render(
      <ComparisonTable
        features={sampleFeatures}
        usName="Our Product"
        themName="Competitor"
      />,
    );

    expect(screen.getByText('React 18')).toBeInTheDocument();
    expect(screen.getByText('Vue 3')).toBeInTheDocument();
  });

  it('has an accessible table with aria-label', () => {
    render(
      <ComparisonTable
        features={sampleFeatures}
        usName="Our Product"
        themName="Competitor"
      />,
    );

    const table = screen.getByRole('table');
    expect(table).toHaveAttribute(
      'aria-label',
      'Feature comparison between Our Product and Competitor',
    );
  });

  it('renders column headers with product names', () => {
    render(
      <ComparisonTable
        features={sampleFeatures}
        usName="Our Product"
        themName="Competitor"
      />,
    );

    expect(screen.getByText('Our Product')).toBeInTheDocument();
    expect(screen.getByText('Competitor')).toBeInTheDocument();
    expect(screen.getByText('Feature')).toBeInTheDocument();
  });
});
