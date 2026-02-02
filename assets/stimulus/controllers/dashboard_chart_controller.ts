// @ts-ignore
import { Controller } from '@hotwired/stimulus';
import ApexCharts from 'apexcharts'

export default class extends Controller {
  static values = {
    usersCountTotal: Number,
    usersCountPreferenceNotificationIssueCreated: Number,
    usersCountPreferenceNotificationIssueUpdated: Number,
    usersCountPreferenceNotificationCommentCreated: Number,
    usersCountPreferenceNotificationCommentUpdated: Number,
  };

  static targets = ["notificationPreferenceChart"];

  declare readonly usersCountTotalValue: number;
  declare readonly usersCountPreferenceNotificationIssueCreatedValue: number;
  declare readonly usersCountPreferenceNotificationIssueUpdatedValue: number;
  declare readonly usersCountPreferenceNotificationCommentCreatedValue: number;
  declare readonly usersCountPreferenceNotificationCommentUpdatedValue: number;
  declare readonly notificationPreferenceChartTarget: HTMLDivElement;

  connect(): void {
    console.log("Dashboard Preference Notification controller connected.");
    this.generateNotificationPreferenceChart();
  }

  public generateNotificationPreferenceChart() {
    const options = {
			chart: {
				height: 280,
				type: "radialBar"
			},
			series: [
				Math.round((this.usersCountPreferenceNotificationIssueCreatedValue / this.usersCountTotalValue) * 100),
				Math.round((this.usersCountPreferenceNotificationIssueUpdatedValue / this.usersCountTotalValue) * 100),
				Math.round((this.usersCountPreferenceNotificationCommentCreatedValue / this.usersCountTotalValue) * 100),
				Math.round((this.usersCountPreferenceNotificationCommentUpdatedValue / this.usersCountTotalValue) * 100),
			],
			plotOptions: {
				radialBar: {
					dataLabels: {
						total: {
							show: true,
              label: "Total",
              formatter: () => {
                return `${this.usersCountTotalValue}`;
              }
						}
					}
				}
			},
			labels: [
        'Issue Created',
        'Issue Updated',
        'Comment Created',
        'Comment Updated',
      ],
		}

		const chart = new ApexCharts(this.notificationPreferenceChartTarget, options);
		chart.render();
  }

}
