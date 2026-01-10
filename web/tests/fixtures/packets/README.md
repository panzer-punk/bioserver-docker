# Dnas Packet Fixtures

This directory contains real packet fixtures used for testing Dnas protocol communication. These fixtures include actual request packets and their corresponding response packets captured from Dnas server interactions.

## Directory Structure

- `requests/` - Contains Dnas request packets
- `responses/` - Contains Dnas response packets corresponding to the requests

## File Naming Convention

Packet files follow a standardized naming format:

```
{filetype}_{folder}_{action}_{prefix}_{size}_{test_type}.bin
```

### Components

- **`filetype`**: Either `request` or `response` indicating the packet direction
- **`folder`**: The Dnas protocol folder/namespace (e.g., `gai-gw`)
- **`action`**: Packet category or variant (e.g., `regular`, `others`)
- **`prefix`**: Hexadecimal identifier unique to the packet (e.g., `0118000000000000`)
- **`size`**: Packet size in bytes (e.g., `308bytes`, `184bytes`)
- **`test_type`**: Test classification:
  - `success` - Positive test case (expected successful response)
  - `failure` - Negative test case (expected error/failure response)

### Examples

- `request_gai-gw_regular_0118000000000000_308bytes_success.bin`
  - Request packet, `gai-gw` folder, `regular` action, prefix `0118000000000000`, 308 bytes, positive test case
  
- `response_gai-gw_regular_011800050008498c_328bytes_success.bin`
  - Response packet, `gai-gw` folder, `regular` action, prefix `011800050008498c`, 328 bytes, positive test case

## Usage in Tests

These fixtures are used in test cases to verify that the application correctly handles real Dnas protocol packets. Tests load these binary files and compare actual server responses against expected responses to ensure protocol compliance.
