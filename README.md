# prooph software Event Sourcing Workshop

## Installation

```bash
docker run --rm -it -v $(pwd):/app prooph/composer:7.1 install
dokcer-compose up -d
docker-compose run php php scripts/create_event_stream.php
```