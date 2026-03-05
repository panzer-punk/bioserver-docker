#/bin/sh
set -e

if test "" != "x" ; then
    LD_LIBRARY_PATH="/opt/apache/lib:$LD_LIBRARY_PATH"
else
    LD_LIBRARY_PATH="/opt/apache/lib"
fi
LD_LIBRARY_PATH="/opt/openssl-1.0.2/lib:$LD_LIBRARY_PATH"
export LD_LIBRARY_PATH

CERT_FILES="
/etc/dnas/ca-cert.pem
/etc/dnas/cert.pem
/etc/dnas/cert-key.pem
"

present_count=0
for cert_file in $CERT_FILES; do
    if [ -f "$cert_file" ]; then
        present_count=$((present_count + 1))
    fi
done

if [ "$present_count" -eq 0 ]; then
    echo "Gateway certificates are missing; generating fresh certificates..."
    /var/www/reissue-certs.sh
elif [ "$present_count" -ne 3 ]; then
    echo "Warning: found partial gateway certificate set in /etc/dnas."
    echo "Skipping auto-generation and continuing startup with current files."
fi

/opt/gateway/bin/apachectl -D FOREGROUND