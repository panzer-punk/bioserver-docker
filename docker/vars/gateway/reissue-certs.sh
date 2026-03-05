#!/bin/sh
set -eu

SCRIPT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)
CERT_DIR="/etc/dnas"
CERT_DAYS="${CERT_DAYS:-3650}"

usage() {
  echo "Usage: $0 [--days DAYS]" >&2
  exit 1
}

if [ "$#" -gt 0 ]; then
  case "$1" in
    -d|--days)
      [ "$#" -eq 2 ] || usage
      CERT_DAYS="$2"
      ;;
    *)
      usage
      ;;
  esac
fi

mkdir -p "$CERT_DIR"

TMP_DIR=$(mktemp -d)
trap 'rm -rf "$TMP_DIR"' EXIT INT TERM

CA_KEY="$TMP_DIR/ca-key.pem"
CA_CERT="$TMP_DIR/ca-cert.pem"
CA_EXT="$TMP_DIR/ca-ext.cnf"
CERT_EXT="$TMP_DIR/cert-ext.cnf"

cat >"$CA_EXT" <<'EOF'
basicConstraints=CA:TRUE
subjectKeyIdentifier=hash
authorityKeyIdentifier=keyid:always
EOF

# Generate a fresh local CA: RSA-1024, SHA1 (same profile as existing chain).
openssl genrsa -out "$CA_KEY" 1024
openssl req -new \
  -key "$CA_KEY" \
  -subj "/C=RU/O=Panzer Punk/OU=Mad Coders Authority/CN=gateway-local-ca" \
  -out "$TMP_DIR/ca.csr"
openssl x509 -req \
  -in "$TMP_DIR/ca.csr" \
  -signkey "$CA_KEY" \
  -sha1 \
  -days "$CERT_DAYS" \
  -extfile "$CA_EXT" \
  -out "$CA_CERT"

DOMAINS="
gate1.jp.dnas.playstation.org
gate1.eu.dnas.playstation.org
gate1.us.dnas.playstation.org
gate1.jp.dnas.playstaion.org
ts01.jp.dnas.playstation.org
dnns-p01.jp.dnas.playstation.org
dnns-r01.jp.dnas.playstation.org
bbn01.jp.dnas.playstation.org
bbn02.jp.dnas.playstation.org
"

PRIMARY_CN="gate1.jp.dnas.playstation.org"
CERT_KEY="$TMP_DIR/cert-key.pem"
CERT_CSR="$TMP_DIR/cert.csr"
CERT_CRT="$TMP_DIR/cert.pem"

SAN=""
for domain in $DOMAINS; do
  if [ -z "$SAN" ]; then
    SAN="DNS:$domain"
  else
    SAN="$SAN,DNS:$domain"
  fi
done

cat >"$CERT_EXT" <<EOF
basicConstraints=CA:FALSE
subjectAltName=$SAN
EOF

# Single server cert that covers every configured gateway domain.
openssl genrsa -out "$CERT_KEY" 1024
openssl req -new \
  -key "$CERT_KEY" \
  -subj "/C=RU/O=Panzer Punk/CN=$PRIMARY_CN/emailAddress=admin@example.local" \
  -out "$CERT_CSR"
openssl x509 -req \
  -in "$CERT_CSR" \
  -CA "$CA_CERT" \
  -CAkey "$CA_KEY" \
  -CAcreateserial \
  -sha256 \
  -days "$CERT_DAYS" \
  -extfile "$CERT_EXT" \
  -out "$CERT_CRT"

install -m 0644 "$CA_CERT" "$CERT_DIR/ca-cert.pem"
install -m 0600 "$CERT_KEY" "$CERT_DIR/cert-key.pem"
install -m 0644 "$CERT_CRT" "$CERT_DIR/cert.pem"

echo "Gateway SAN certificate generated in $CERT_DIR (days=$CERT_DAYS)"
