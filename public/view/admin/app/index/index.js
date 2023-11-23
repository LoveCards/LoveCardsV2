class Index extends Base {

    constructor() {
        super();
        this.hooks = {};
        this.chart = {}
    }

    //外部

    /**
     * 绑定渲染画布
     * @param {String} canvasId 
     * @param {JSON} jsonData //遇到问题请查询数据结构
     */
    BindJsonLineChart = (canvasId, jsonData) => {
        //绑定方法

        //格式化数据
        let chartLabels = new Array();
        let chartDatasets = new Array();

        //取横坐标
        for (const index in jsonData[0]['data']['date']) {
            chartLabels.push(jsonData[0]['data']['date'][index]);
        }
        //取数据坐标
        for (const index in jsonData) {
            let nowData = new Array();
            for (const countIndex in jsonData[index]['data']['count']) {
                nowData.push(jsonData[index]['data']['count'][countIndex]);
            }
            chartDatasets.push({
                'label': jsonData[index]['label'],
                'data': nowData
            });
        }

        //渲染图表
        this.RenderingLineChart(canvasId, chartLabels, chartDatasets);
    }

    //内部

    /**
     * 渲染图表
     * @param {String} canvasId 
     * @param {Array} chartLabels 
     * @param {Array} chartDatasets 
     */
    RenderingLineChart = (canvasId, chartLabels, chartDatasets) => {
        let ColorArray = ['33,150,243', '253,216,53', '229,57,35']
        chartDatasets.forEach((element, index) => {
            if (index >= ColorArray.length) {
                const R = Math.floor(Math.random() * (255)) + 1;
                const G = Math.floor(Math.random() * (255)) + 1;
                const B = Math.floor(Math.random() * (255)) + 1;
                ColorArray[index] = R + ',' + G + ',' + B;
            }
            if (element.backgroundColor == undefined) {
                element.backgroundColor = ['rgba(' + ColorArray[index] + ',0.5)'];
                element.borderColor = ['rgba(' + ColorArray[index] + ',1)'];
            }
        });
        //绘制图表
        var ctx = document.getElementById(canvasId).getContext('2d');
        var myChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: chartLabels,
                datasets: chartDatasets
            },
            options: {
                borderWidth: 1
            }
        });
    }
}