#!/usr/bin/env bash
set -euo pipefail

# Stripe CLI webhook simulation script for local dev
#
# Requirements:
# - Stripe CLI installed and logged in: https://stripe.com/docs/stripe-cli
# - Your app running locally and accessible at $FORWARD_TO
# - WEBHOOK_SECRET configured in your .env as services.stripe.webhook.secret (matches stripe listen secret)
#
# Usage:
#   ./scripts/stripe-webhook-sim.sh [--forward-to http://localhost/api/webhooks/stripe]

FORWARD_TO="http://localhost/api/webhooks/stripe"
if [[ "${1:-}" == "--forward-to" && -n "${2:-}" ]]; then
  FORWARD_TO="$2"
fi

echo "Forwarding Stripe events to: $FORWARD_TO"

echo "Starting stripe listen in background..."
stripe listen --forward-to "$FORWARD_TO" >/tmp/stripe-listen.log 2>&1 &
LISTEN_PID=$!
trap 'kill $LISTEN_PID >/dev/null 2>&1 || true' EXIT

sleep 1

echo "Triggering checkout.session.completed (credit purchase flow requires proper metadata in app flow)"
stripe trigger checkout.session.completed >/dev/null || true

echo "Triggering checkout.session.expired"
stripe trigger checkout.session.expired >/dev/null || true

echo "Done. Check your app logs for webhook handling output."

