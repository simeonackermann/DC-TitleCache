#!/bin/sh

# Start JMeter Benchmark on a TitleCache

: ${GRAPH:="http://titlecache/"}
: ${CACHES:=filesystem}
CONFDIR=/jmeter/config/
PAYLOAD500=${CONFDIR}payload500.txt
PAYLOAD100000=${CONFDIR}payload100000.txt
PAYLOAD250000=${CONFDIR}payload250000.txt

# better wait a second (may dockerizing-wait comes to fast)
sleep 1

if [[ -z $1 ]]; then
    echo "[ERROR] missing JMeter file. Call: ./run.sh path/to/file.jmx"
    exit 1
fi

#jmfiles=$@
jmfile=$1

if [[ ! -f $jmfile ]]; then
	echo "[WARNING] File \"${jmfile}\" not found. Skip this test"
fi

# cleaning old results
# rm -f /jmeter/results/*

for cache in ${CACHES}
do
	echo "============================================"
	echo "[INFO] Running Cache: $cache"
	cur_jmfile=${jmfile}_${cache}

	cp $jmfile $cur_jmfile

	sed -i "s|%CACHE-NAME%|$cache|" $cur_jmfile
	sed -i "s|%GRAPH%|$GRAPH|" $cur_jmfile
	sed -i "s|%PAYLOAD500%|$PAYLOAD500|" $cur_jmfile
	sed -i "s|%PAYLOAD100000%|$PAYLOAD100000|" $cur_jmfile
	sed -i "s|%PAYLOAD250000%|$PAYLOAD250000|" $cur_jmfile

	echo "[INFO] starting JMeter Benchmark ..."
	jmeter -n -t $cur_jmfile

	echo "[INFO] Result file: jmeter-results-${cache}.csv"
	mv /jmeter/results/jmeter-result.csv /jmeter/results/jmeter-result-${cache}.csv
	echo ""
	
	rm $cur_jmfile
done

# set user rights
chmod -R 777 /jmeter/results/

echo "[INFO] Done and bye bye!"

# tail -f /dev/null
