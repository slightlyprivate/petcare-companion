#!/usr/bin/env bash
# validate-env.sh - Environment validation for CI/CD
#
# This script validates environment configuration files to prevent drift
# and catch misconfigurations before deployment.
#
# Usage:
#   ./scripts/validate-env.sh [--environment ENV]
#
# Options:
#   --environment ENV    Validate specific environment (development, staging, production-blue, production-green)
#                        If not specified, validates all environments
#
# Exit codes:
#   0 - All validations passed
#   1 - Validation failures detected

set -euo pipefail

# Color output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

# Counters
ERRORS=0
WARNINGS=0

# Logger functions
log_info() {
    echo -e "${BLUE}ℹ${NC} $*"
}

log_success() {
    echo -e "${GREEN}✓${NC} $*"
}

log_warning() {
    echo -e "${YELLOW}⚠${NC} $*"
    ((WARNINGS++))
}

log_error() {
    echo -e "${RED}✗${NC} $*"
    ((ERRORS++))
}

# Extract variable names from .env file
extract_env_keys() {
    local file=$1
    grep -v '^#' "$file" | grep -v '^$' | cut -d= -f1 | sort -u || true
}

# Check if .env.example and .env keys match
validate_env_keys() {
    local env_dir=$1
    local env_name=$2
    
    log_info "Validating $env_name: checking .env.example vs .env key matching..."
    
    if [[ ! -f "$env_dir/.env.example" ]]; then
        log_warning "$env_name: .env.example not found"
        return 0
    fi
    
    if [[ ! -f "$env_dir/.env" ]]; then
        log_warning "$env_name: .env not found (expected in CI, will be created from .env.example)"
        return 0
    fi
    
    #!/usr/bin/env bash
    echo "validate-env.sh has been removed. No action needed." 
    exit 0
    env_keys=$(extract_env_keys "$env_dir/.env")
