import { Head } from "@inertiajs/react";

import { AreaChart, BarChart, LineChart, PieChart } from "@/Components/ui/charts";
import DashboardLayout from "@/Layouts/DashboardLayout";

const revenueData = [
  { month: "Jan", revenue: 4000, expenses: 2400 },
  { month: "Feb", revenue: 3000, expenses: 1398 },
  { month: "Mar", revenue: 2000, expenses: 9800 },
  { month: "Apr", revenue: 2780, expenses: 3908 },
  { month: "May", revenue: 1890, expenses: 4800 },
  { month: "Jun", revenue: 2390, expenses: 3800 },
  { month: "Jul", revenue: 3490, expenses: 4300 },
];

const categoryData = [
  { name: "Electronics", value: 400 },
  { name: "Clothing", value: 300 },
  { name: "Food", value: 300 },
  { name: "Books", value: 200 },
  { name: "Other", value: 100 },
];

const visitorsData = [
  { day: "Mon", visitors: 120, pageViews: 340 },
  { day: "Tue", visitors: 150, pageViews: 420 },
  { day: "Wed", visitors: 180, pageViews: 510 },
  { day: "Thu", visitors: 140, pageViews: 380 },
  { day: "Fri", visitors: 200, pageViews: 560 },
  { day: "Sat", visitors: 90, pageViews: 250 },
  { day: "Sun", visitors: 70, pageViews: 200 },
];

export default function Charts() {
  return (
    <DashboardLayout>
      <Head title="Charts" />

      <div className="container py-8">
        <div className="mb-8">
          <h1 className="text-2xl font-bold">Charts</h1>
          <p className="mt-1 text-muted-foreground">
            Theme-aware chart components powered by Recharts.
          </p>
        </div>

        <div className="grid gap-8 lg:grid-cols-2">
          <section aria-labelledby="chart-revenue" className="rounded-lg border bg-card p-6">
            <h2 id="chart-revenue" className="mb-4 text-lg font-semibold">Revenue vs Expenses</h2>
            <AreaChart
              data={revenueData}
              xKey="month"
              yKeys={["revenue", "expenses"]}
              showLegend
              aria-label="Area chart showing revenue vs expenses by month"
            />
          </section>

          <section aria-labelledby="chart-visitors" className="rounded-lg border bg-card p-6">
            <h2 id="chart-visitors" className="mb-4 text-lg font-semibold">Weekly Visitors</h2>
            <BarChart
              data={visitorsData}
              xKey="day"
              yKeys={["visitors", "pageViews"]}
              showLegend
              aria-label="Bar chart showing weekly visitors and page views"
            />
          </section>

          <section aria-labelledby="chart-categories" className="rounded-lg border bg-card p-6">
            <h2 id="chart-categories" className="mb-4 text-lg font-semibold">Category Breakdown</h2>
            <PieChart
              data={categoryData}
              innerRadius={60}
              aria-label="Pie chart showing category breakdown"
            />
          </section>

          <section aria-labelledby="chart-traffic" className="rounded-lg border bg-card p-6">
            <h2 id="chart-traffic" className="mb-4 text-lg font-semibold">Traffic Trend</h2>
            <LineChart
              data={visitorsData}
              xKey="day"
              yKeys={["visitors", "pageViews"]}
              showLegend
              aria-label="Line chart showing traffic trend by day"
            />
          </section>
        </div>
      </div>
    </DashboardLayout>
  );
}
