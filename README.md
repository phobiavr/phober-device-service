## Device-service

The `device-service` within the <b>Phober</b> project focuses on displaying hardware-related data, device
configurations, and game information.

### Responsibilities:

- Displays hardware data, device configurations, and game information.
- Does not manage data or configurations but serves as a viewer.

### Dependencies:

- Relies on the `config-server` and `adminpanel` services for initial setup and configuration.
- Utilizes the `phober_device` database for data access.

### Additional Details:

- Utilizes multiple instances (`replicas: 3`) for redundancy but does not function as a load balancer.
- Provides an interface to view hardware-related data within the application environment.
