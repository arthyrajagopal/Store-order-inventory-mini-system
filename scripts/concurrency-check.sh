#!/usr/bin/env bash
# Real multi-process concurrency proof.
#
# Usage:
#   1. php artisan serve
#   2. php artisan tinker --execute="App\Models\Product::factory()->create(['stock_on_hand'=>1,'code'=>'RACE-1'])"
#   3. Note the printed product id, then run:
#        ./scripts/concurrency-check.sh <product_id>
#
# Fires two order-creation requests for the SAME product at (as close to)
# the same instant as `curl` in parallel allows. Expect: one HTTP 201, one
# HTTP 409, and the product's stock_on_hand ends at 0 (never negative).

set -e
PRODUCT_ID=${1:?Usage: concurrency-check.sh <product_id>}

BODY='{"customer_name":"Race Tester","customer_email":"race@example.com","items":[{"product_id":'"$PRODUCT_ID"',"quantity":1}]}'

curl -s -o /tmp/resp_a.json -w "A: %{http_code}\n" -X POST http://127.0.0.1:8000/api/orders \
  -H "Content-Type: application/json" -d "$BODY" &

curl -s -o /tmp/resp_b.json -w "B: %{http_code}\n" -X POST http://127.0.0.1:8000/api/orders \
  -H "Content-Type: application/json" -d "$BODY" &

wait
echo "--- Response A ---"; cat /tmp/resp_a.json; echo
echo "--- Response B ---"; cat /tmp/resp_b.json; echo
