import argparse
import sys

from acct_parse import Account

# Example
# python ~/scripts/acct_print.py "jobnumber:exit_status:mem.mem_req_tot:mem.mem_used:time.time_taken_td" --start 1235367 --end 1236460 [--failed]
if __name__ == "__main__":

    parser = argparse.ArgumentParser()
    parser.add_argument("fmt_string", default="jobnumber:hostname:jobname:exit_status")
    parser.add_argument("--acct_path", default="/cm/shared/apps/sge/6.2u5p2/default/common/accounting")
    parser.add_argument("--start", type=int)
    parser.add_argument("--end", type=int)
    parser.add_argument("-f", "--failed", action="store_true")

    args = parser.parse_args()
    if args.start and args.end:
        if args.start > args.end:
            print("[FAIL] --start larger than --end, no jobs will be matched.")
            sys.exit(1)

    acct = Account(args.acct_path)

    for jid, job in sorted(acct.jobs.items()):
        jid = int(jid)
        if args.start:
            if jid < args.start:
                continue
        if args.end:
            if jid > args.end:
                continue
        if args.failed:
            if job["exit_status"] == 0:
                continue

        print acct.print_job(jid, args.fmt_string)
