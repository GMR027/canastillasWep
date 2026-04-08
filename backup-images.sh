#!/bin/bash
set -e

NAMESPACE="canastillas"
REMOTE_PATH="/var/www/public/image"
BACKUP_DIR="backup-images"

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
mkdir -p "$BACKUP_DIR"

echo ">>> Copying ${REMOTE_PATH} -> ${BACKUP_DIR}/"
kubectl cp "${NAMESPACE}/${POD}:${REMOTE_PATH}/." "$BACKUP_DIR"

echo ">>> Backup complete: ${BACKUP_DIR}"
