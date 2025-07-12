// eslint.config.js
import { defineConfig } from "eslint/config";

export default defineConfig([
	{
		files: ["**/*.ts", "**/*.cts", "**.*.mts"],
		rules: {
			"no-var": "error",
		},
	},
]);
