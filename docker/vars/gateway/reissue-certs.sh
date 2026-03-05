#!/bin/sh
set -eu

SCRIPT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
CERT_DIR="/etc/dnas"
CERT_DAYS="${CERT_DAYS:-3650}"

usage() {
  echo "Usage: $0 [--days DAYS]" >&2
  exit 1
}

while [ "$#" -gt 0 ]; do
  case "$1" in
    -d|--days)
      [ "$#" -eq 2 ] || usage
      CERT_DAYS="$2"
      shift 2
      ;;
    *)
      usage
      ;;
  esac
done

mkdir -p "$CERT_DIR"

TMP_DIR=$(mktemp -d)
trap 'rm -rf "$TMP_DIR"' EXIT INT TERM

CA_KEY="$TMP_DIR/ca-key.pem"
CA_CERT="$TMP_DIR/ca-cert.pem"
CA_EXT="$TMP_DIR/ca-ext.cnf"

cat >"$CA_EXT" <<'EOF'
basicConstraints=CA:TRUE
subjectKeyIdentifier=hash
authorityKeyIdentifier=keyid:always
EOF

# Generate a fresh local CA: RSA-1024, SHA1 (same profile as existing chain).
# DO NOT CHANGE THE CA DETAILS - it must match the existing chain for compatibility.
openssl genrsa -out "$CA_KEY" 1024
openssl req -new \
  -key "$CA_KEY" \
  -subj "/C=US/O=VeriSign, Inc./OU=Class 3 Public Primary Certification Authority" \
  -out "$TMP_DIR/ca.csr"
openssl x509 -req \
  -in "$TMP_DIR/ca.csr" \
  -signkey "$CA_KEY" \
  -sha1 \
  -days "$CERT_DAYS" \
  -extfile "$CA_EXT" \
  -out "$CA_CERT"

PRIMARY_CN="gate1.jp.dnas.playstation.org"
CERT_KEY="$TMP_DIR/cert-key.pem"
CERT_CSR="$TMP_DIR/cert.csr"
CERT_CRT="$TMP_DIR/cert.pem"


# Single server cert without SAN for legacy DNAS compatibility.
openssl genrsa -out "$CERT_KEY" 1024
openssl req -new \
  -key "$CERT_KEY" \
  -subj "/C=JP/O=Panzer Punk/CN=$PRIMARY_CN/emailAddress=panzer_punk@1312.punk" \
  -out "$CERT_CSR"
openssl x509 -req \
  -in "$CERT_CSR" \
  -CA "$CA_CERT" \
  -CAkey "$CA_KEY" \
  -CAcreateserial \
  -sha256 \
  -days "$CERT_DAYS" \
  -out "$CERT_CRT"

install -m 0644 "$CA_CERT" "$CERT_DIR/ca-cert.pem"
install -m 0600 "$CERT_KEY" "$CERT_DIR/cert-key.pem"
install -m 0644 "$CERT_CRT" "$CERT_DIR/cert.pem"

echo "Gateway certificate generated in $CERT_DIR (days=$CERT_DAYS)"
