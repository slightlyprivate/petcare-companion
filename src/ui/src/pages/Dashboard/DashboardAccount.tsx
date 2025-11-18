/**
 * Dashboard account management page.
 * @returns Dashboard account management component
 */
export default function DashboardAccount() {
  return (
    <div>
      <h1 className="text-xl font-semibold mb-3">My Account</h1>
      <div className="space-y-2 text-sm">
        <div>Profile</div>
        <div>Security</div>
        <div>Notifications</div>
        <div>Privacy</div>
      </div>
    </div>
  );
}
