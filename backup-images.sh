#!/bin/bash
set -e

NAMESPACE="canastillas"
REMOTE_PATH="/var/www/public/image"
BACKUP_DIR="backup-images"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
DEST="${BACKUP_DIR}/${TIMESTAMP}"

echo ">>> Finding a running pod in namespace '${NAMESPACE}'..."
POD=$(kubectl get pods -n "$NAMESPACE" \
  --field-selector=status.phase=Running \
  -o jsonpath='{.items[0].metadata.name}' 2>/dev/null)

if [ -z "$POD" ]; then
  echo "ERROR: No running pods found in namespace '${NAMESPACE}'."
  exit 1
fi

echo ">>> Using pod: ${POD}"
mkdir -p "$DEST"

echo ">>> Copying ${REMOTE_PATH} -> ${DEST}/"
kubectl cp "${NAMESPACE}/${POD}:${REMOTE_PATH}" "$DEST"

echo ">>> Backup complete: ${DEST}"
