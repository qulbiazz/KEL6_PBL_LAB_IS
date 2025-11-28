#!/bin/bash

# Database Export Script for Deployment
# Usage: ./export_database.sh

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${BLUE}==================================${NC}"
echo -e "${BLUE}  Database Export Script${NC}"
echo -e "${BLUE}==================================${NC}"
echo ""

# Load .env file
if [ -f .env ]; then
    export $(cat .env | grep -v '^#' | xargs)
else
    echo -e "${RED}Error: .env file not found!${NC}"
    exit 1
fi

# Create database directory if not exists
mkdir -p database/exports

# Export full database schema and data
echo -e "${GREEN}1. Exporting full database...${NC}"
pg_dump -h $DB_HOST -p $DB_PORT -U $DB_USER -d $DB_NAME \
    -f database/exports/full_database_$(date +%Y%m%d_%H%M%S).sql

# Export schema only
echo -e "${GREEN}2. Exporting schema only...${NC}"
pg_dump -h $DB_HOST -p $DB_PORT -U $DB_USER -d $DB_NAME \
    --schema-only \
    -f database/exports/schema_only_$(date +%Y%m%d_%H%M%S).sql

# Export data only
echo -e "${GREEN}3. Exporting data only...${NC}"
pg_dump -h $DB_HOST -p $DB_PORT -U $DB_USER -d $DB_NAME \
    --data-only \
    -f database/exports/data_only_$(date +%Y%m%d_%H%M%S).sql

# Export specific tables for production deployment
echo -e "${GREEN}4. Exporting essential tables...${NC}"
pg_dump -h $DB_HOST -p $DB_PORT -U $DB_USER -d $DB_NAME \
    -t admins \
    -t students \
    -t equipment \
    -t borrowing \
    -t attendance \
    -t team_members \
    -t banners \
    -t news \
    -t gallery \
    -t research_activities \
    -t partners \
    -t products \
    -t cards \
    -t guestbook \
    --schema-only \
    -f database/exports/essential_schema_$(date +%Y%m%d_%H%M%S).sql

echo ""
echo -e "${GREEN}==================================${NC}"
echo -e "${GREEN}  Export Complete!${NC}"
echo -e "${GREEN}==================================${NC}"
echo ""
echo "Exported files in: database/exports/"
ls -lh database/exports/ | tail -5
echo ""
echo -e "${BLUE}Next steps:${NC}"
echo "1. Upload SQL files to AlwaysData server"
echo "2. Import using: psql -h HOST -U USER DATABASE < file.sql"
