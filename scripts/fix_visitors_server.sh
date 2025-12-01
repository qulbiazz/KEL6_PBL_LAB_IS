#!/bin/bash

# ============================================================================
# QUICK FIX SCRIPT - Repair Visitors Table on Server
# ============================================================================
# Purpose: Fix duplicate key constraint error on visitors table
# Usage: ./scripts/fix_visitors_server.sh
# ============================================================================

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}  INLET - Fix Visitors Table on Server${NC}"
echo -e "${YELLOW}========================================${NC}"
echo ""

# Configuration (UPDATE THESE VALUES)
SSH_HOST="pbl-inlet.alwaysdata.net"
SSH_USER="pbl-inlet"
DB_HOST="postgresql-pbl-inlet.alwaysdata.net"
DB_USER="pbl-inlet"
DB_NAME="pbl-inlet_inlet_db"
REMOTE_PATH="www/inlet-web"

# Step 1: Upload migration file
echo -e "${GREEN}[1/4] Uploading migration file to server...${NC}"
scp database/migrations/fix_visitors_unique_constraint.sql \
    ${SSH_USER}@${SSH_HOST}:~/${REMOTE_PATH}/database/migrations/

# Step 2: Upload updated VisitorModel.php
echo -e "${GREEN}[2/4] Uploading updated VisitorModel.php...${NC}"
scp models/VisitorModel.php \
    ${SSH_USER}@${SSH_HOST}:~/${REMOTE_PATH}/models/

# Step 3: Run migration on server
echo -e "${GREEN}[3/4] Running migration on server database...${NC}"
ssh ${SSH_USER}@${SSH_HOST} << 'ENDSSH'
    cd www/inlet-web
    echo "Applying fix_visitors_unique_constraint.sql..."
    psql -h postgresql-pbl-inlet.alwaysdata.net \
         -U pbl-inlet \
         -d pbl-inlet_inlet_db \
         -f database/migrations/fix_visitors_unique_constraint.sql
ENDSSH

# Step 4: Clear PHP cache/opcache
echo -e "${GREEN}[4/4] Clearing cache...${NC}"
ssh ${SSH_USER}@${SSH_HOST} << 'ENDSSH'
    cd www/inlet-web
    # Clear any cached files
    find . -name "*.cache" -type f -delete 2>/dev/null || true
    echo "Cache cleared!"
ENDSSH

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  ✓ Fix Applied Successfully!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "Next steps:"
echo "1. Test the website: https://pbl-inlet.alwaysdata.net/inlet-web/"
echo "2. Check for any errors in browser console"
echo "3. Verify visitor tracking is working"
echo ""
echo "If you still see errors, run:"
echo "  ssh ${SSH_USER}@${SSH_HOST}"
echo "  cd ${REMOTE_PATH}"
echo "  tail -f logs/error.log"
echo ""
