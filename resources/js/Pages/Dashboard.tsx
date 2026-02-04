import { Head } from "@inertiajs/react";
import { Activity, Users, CreditCard, TrendingUp } from "lucide-react";
import DashboardLayout from "@/Layouts/DashboardLayout";
import PageHeader from "@/Components/layout/PageHeader";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";

// Placeholder stats - replace with real data
const stats = [
  {
    title: "Total Users",
    value: "0",
    description: "Active accounts",
    icon: Users,
    trend: null,
  },
  {
    title: "Revenue",
    value: "$0",
    description: "This month",
    icon: CreditCard,
    trend: null,
  },
  {
    title: "Active Sessions",
    value: "0",
    description: "Currently online",
    icon: Activity,
    trend: null,
  },
  {
    title: "Growth",
    value: "0%",
    description: "vs last month",
    icon: TrendingUp,
    trend: null,
  },
];

export default function Dashboard() {
  return (
    <DashboardLayout>
      <Head title="Dashboard" />

      <PageHeader
        title="Dashboard"
        subtitle="Welcome to your application dashboard"
      />

      <div className="container py-8">
        {/* Stats Grid */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-8">
          {stats.map((stat) => (
            <Card key={stat.title}>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">{stat.title}</CardTitle>
                <stat.icon className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{stat.value}</div>
                <p className="text-xs text-muted-foreground">{stat.description}</p>
              </CardContent>
            </Card>
          ))}
        </div>

        {/* Main Content Area */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
          <Card className="col-span-4">
            <CardHeader>
              <CardTitle>Overview</CardTitle>
              <CardDescription>
                Your activity overview for this period.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="h-[200px] flex items-center justify-center text-muted-foreground">
                {/* Placeholder for chart */}
                <p>Chart placeholder - integrate your preferred charting library</p>
              </div>
            </CardContent>
          </Card>

          <Card className="col-span-3">
            <CardHeader>
              <CardTitle>Recent Activity</CardTitle>
              <CardDescription>
                Latest actions in your account.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {/* Placeholder for activity feed */}
                <p className="text-sm text-muted-foreground">
                  No recent activity to show.
                </p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </DashboardLayout>
  );
}
