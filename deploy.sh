#!/bin/bash
set -e

VERSION_FILE=".version"
REGISTRY="gmr27"
REPOSITORY="canastillas"
NAMESPACE="canastillas"
HELM_RELEASE="canastillas"
HELM_CHART="./deployment"
VALUES_LOCAL="./deployment/values.local.yaml"

if [ -f "$VERSION_FILE" ]; then
  VERSION=$(cat "$VERSION_FILE")
else
  VERSION=0
fi

VERSION=$((VERSION + 1))
echo "$VERSION" > "$VERSION_FILE"

IMAGE="${REGISTRY}/${REPOSITORY}:${VERSION}"
echo ">>> Building image: ${IMAGE}"
docker build -t "$IMAGE" .

echo ">>> Pushing image: ${IMAGE}"
docker push "$IMAGE"

echo ">>> Deploying version ${VERSION} via Helm..."
if [ -f "$VALUES_LOCAL" ]; then
  helm upgrade --install "$HELM_RELEASE" "$HELM_CHART" \
    --namespace "$NAMESPACE" \
    -f "$VALUES_LOCAL" \
    --set image.tag="$VERSION"
else
  echo "AVISO: no existe ${VALUES_LOCAL}; se usan los valores por defecto."
  helm upgrade --install "$HELM_RELEASE" "$HELM_CHART" \
    --namespace "$NAMESPACE" \
    --set image.tag="$VERSION"
fi

echo ">>> Done. Deployed ${IMAGE}"
