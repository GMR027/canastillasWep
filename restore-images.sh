#!/bin/bash
set -e

NAMESPACE="canastillas"
REMOTE_PATH="/var/www/public/image"
BACKUP_DIR="backup-images"

if [ ! -d "$BACKUP_DIR" ] || [ -z "$(ls -A "$BACKUP_DIR" 2>/dev/null)" ]; then
  echo "ERROR: No backup found in '${BACKUP_DIR}'."
  exit 1
fi

echo ">>> Finding a running pod in namespace '${NAMESPACE}'..."
POD=$(kubectl get pods -n "$NAMESPACE" \
  -l app=canastillas \
  --field-selector=status.phase=Running \
  -o jsonpath='{.items[0].metadata.name}' 2>/dev/null)

if [ -z "$POD" ]; then
  echo "ERROR: No running pods found in namespace '${NAMESPACE}'."
  exit 1
fi

echo ">>> Using pod: ${POD}"
echo ">>> Restoring ${BACKUP_DIR}/ -> ${NAMESPACE}/${POD}:${REMOTE_PATH}"

kubectl cp "${BACKUP_DIR}/." "${NAMESPACE}/${POD}:${REMOTE_PATH}"

echo ">>> Restore complete: ${BACKUP_DIR} -> ${REMOTE_PATH}"
