import './app.css';
import { AppVersionFooter } from './components/AppVersionFooter';

export function App() {
  return (
    <main className="ui-shell">
      <div>
        <header>
          <p className="eyebrow">PetCare Companion · Account UI</p>
          <h1>Account & Household Administration</h1>
          <p className="lede">
            This surface centralizes account management, billing, profile controls, household
            configuration, and compliance tooling for the Slightly Better ecosystem. The caregiving
            experience now lives inside the dedicated PetCare Companion PWA.
          </p>
        </header>
        <section>
          <h2>Roadmap Snapshot</h2>
          <ul>
            <li>Account + authentication settings</li>
            <li>Billing / Stripe portal integrations</li>
            <li>Household and pet profile settings</li>
            <li>Security controls and session management</li>
            <li>Notification and communication preferences</li>
          </ul>
        </section>
        <section>
          <h2>Status</h2>
          <p>
            The UI scaffold is ready for component work. Use this space for administrative screens,
            keep shared primitives under <code>src/lib</code>, and publish only account/billing
            flows here. Ship all caregiving workflows via the PWA Experience UI.
          </p>
        </section>
      </div>
      <AppVersionFooter />
    </main>
  );
}

export default App;
