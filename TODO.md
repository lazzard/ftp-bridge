# TODO

## Next added functionalities.

- [ ] Allows changing the record structure of the transfered files `STRU` - [RFC959][1].
- [ ] Allows changing the port range for active mode connections.
- [ ] Allows using the passive address in the control channel when using the passive mode.
- [ ] Supporting explicit FTP over SSL/TLS - [RFC2228][2].
- [ ] Adding the `FtpBridge::quit` method, `QUIT` - [RFC959][1].
- [ ] Implementing `FtpBridge::reset` method, `REIN` - [RFC959][1].
- [ ] Supporting IPv6 based connections (`EPRT`, `EPSV`) - [RFC2428][3].

[1]: https://datatracker.ietf.org/doc/html/rfc959#section-4
[2]: https://datatracker.ietf.org/doc/html/rfc2228
[3]: https://datatracker.ietf.org/doc/html/rfc2428
