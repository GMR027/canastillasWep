#!/bin/bash
set -e

VERSION_FILE=".version"
REGISTRY="gmr27"
REPOSITORY="canastillas"
NAMESPACE="canastillas"
HELM_RELEASE="canastillas"
HELM_CHART="./deployment"

# Read and increment version
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
helm upgrade --install "$HELM_RELEASE" "$HELM_CHART" \
  --namespace "$NAMESPACE" \
  --set image.tag="$VERSION"

echo ">>> Done. Deployed ${IMAGE}"
