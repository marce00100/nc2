import React from 'react';
import ReactDOM from 'react-dom';

import JqxChart from '../../../jqwidgets-react/react_jqxchart.js';

class App extends React.Component {
    render() {
        let source =
            {
                datatype: 'csv',
                datafields: [
                    { name: 'Date' },
                    { name: 'S&P 500' },
                    { name: 'NASDAQ' }
                ],
                url: '../sampledata/nasdaq_vs_sp500.txt'
            };
        let dataAdapter = new $.jqx.dataAdapter(source, { async: false, autoBind: true, loadError: (xhr, status, error) => { alert('Error loading "' + source.url + '" : ' + error); } });

        let months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        let padding = { left: 10, top: 5, right: 30, bottom: 5  };

        let titlePadding = { left: 10, top: 0, right: 0, bottom: 10 };

        let xAxis =
            {
                dataField: 'Date',
                formatFunction: (value) => {
                    return value.getDate() + '-' + months[value.getMonth()] + '-' + value.getFullYear();
                },
                type: 'date',
                baseUnit: 'month',
                minValue: '01-01-2014',
                maxValue: '01-01-2015',
                unitInterval: 1,
                valuesOnTicks: true,
                gridLines: { interval: 3 },
                labels: {
                    angle: -45,
                    rotationPoint: 'topright',
                    offset: { x: 0, y: -25 }
                }
            };


        let seriesGroups =
            [
                {
                    type: 'line',
                    valueAxis:
                    {
                        title: { text: 'Daily Closing Price<br><br>' }
                    },
                    series: [
                        { dataField: 'S&P 500', displayText: 'S&P 500' },
                        { dataField: 'NASDAQ', displayText: 'NASDAQ' }
                    ]
                }
            ];
        return (
            <JqxChart style={{ width: 850, height: 500 }}
                title={'U.S. Stock Market Index Performance'} description={'NASDAQ Composite compared to S&P 500'}
                showLegend={true} enableAnimations={true} padding={padding} 
                titlePadding={titlePadding} source={dataAdapter} xAxis={xAxis}
                colorScheme={'scheme01'} seriesGroups={seriesGroups} enableCrosshairs={true}
                crosshairsDashStyle={'2,2'} crosshairsLineWidth={1} crosshairsColor={'#888888'}
            />
        )
    }
}

ReactDOM.render(<App />, document.getElementById('app'));
