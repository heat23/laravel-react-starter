import { Check, Minus } from 'lucide-react';

import type { ComparisonFeature } from '@/types/index';

interface ComparisonTableProps {
  features: ComparisonFeature[];
  usName: string;
  themName: string;
}

export function ComparisonTable({
  features,
  usName,
  themName,
}: ComparisonTableProps) {
  const renderCell = (value: string | boolean) => {
    if (typeof value === 'boolean') {
      return value ? (
        <Check className="mx-auto h-5 w-5 text-success" aria-label="Yes" />
      ) : (
        <Minus
          className="mx-auto h-5 w-5 text-muted-foreground"
          aria-label="No"
        />
      );
    }
    return <span>{value}</span>;
  };

  return (
    <div className="overflow-x-auto">
      <table
        className="w-full border-collapse text-left text-sm"
        aria-label={`Feature comparison between ${usName} and ${themName}`}
      >
        <thead>
          <tr className="border-b border-border">
            <th className="py-3 pr-4 font-semibold text-foreground">
              Feature
            </th>
            <th className="px-4 py-3 text-center font-semibold text-primary">
              {usName}
            </th>
            <th className="px-4 py-3 text-center font-semibold text-muted-foreground">
              {themName}
            </th>
          </tr>
        </thead>
        <tbody>
          {features.map((row) => (
            <tr
              key={row.feature}
              className="border-b border-border/50 last:border-0"
            >
              <td className="py-3 pr-4 font-medium text-foreground">
                {row.feature}
              </td>
              <td className="px-4 py-3 text-center">{renderCell(row.us)}</td>
              <td className="px-4 py-3 text-center">{renderCell(row.them)}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
